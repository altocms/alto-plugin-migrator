<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginMigrator_ModuleJoomla_MapperJoomla extends Mapper {

    public function UserReset() {

        $sql = "DELETE FROM ?_user WHERE user_id>1";
        $xResult = $this->oDb->query($sql);
        return $xResult;
    }

    public function UserCheck($sTableUser1) {

        $sql = "SELECT user_id, user_login, user_mail, u1.id, u1.username, u1.email
            FROM ?_user
            INNER JOIN $sTableUser1 AS u1 ON u1.id=user_id OR u1.username=user_login OR u1.email=user_mail
            ";
        return $this->oDb->select($sql);
    }

    public function UserMigrate($sTableUser1, $sTableUser2, $sTableUser3) {

        $sql = "
          INSERT INTO ?_user (
            `user_id`,
            `user_login`,
            `user_password`,
            `user_mail`,
            `user_date_register`,
            `user_profile_name`,
            `user_profile_sex`,
            `user_profile_about`,
            `user_profile_avatar`,
            `user_activate`
            )
          SELECT
            u1.`id`,
            u1.`username`,
            CONCAT('Jx:', `password`),
            `email`,
            `registerDate`,
            `name`,
            CASE WHEN u2.gender='m' THEN 'man' ELSE 'woman' END,
            u2.description,
            CONCAT('@uploads/', u3.avatar),
            1
          FROM $sTableUser1 AS u1
          LEFT JOIN $sTableUser2 AS u2 ON u2.userId=u1.id
          LEFT JOIN $sTableUser3 AS u3 ON u3.userid=u1.id
        ";
        $xResult = $this->oDb->query($sql);
        if ($xResult) {
            $aChangedUsers = $this->UserFixLogin();
        } else {
            $aChangedUsers = array();
        }
        if ($xResult !== false) {
            $sql = "SELECT COUNT(*) FROM ?_user";
            $nCount = $this->oDb->selectCell($sql);
            return array('count' => $nCount, 'changed' => $aChangedUsers);
        }
        return false;
    }

    public function UserFixLogin() {

        $sql="SELECT user_id AS ARRAY_KEY, user_login FROM ?_user WHERE user_login REGEXP '[^a-zA-Z0-9\\_\\-]' ORDER BY user_id";
        $aUsers = $this->oDb->selectCol($sql);
        if ($aUsers) {
            $aChangedUsers = array();
            $aTransform = array();
            foreach($aUsers as $nUserId => $sOldLogin) {
                $sUserLogin=strtolower(F::TranslitUrl(trim($sOldLogin)));
                if ($n = array_search($sUserLogin, $aChangedUsers)) {
                    $sUserLogin = $sUserLogin . '-' . $nUserId;
                    $aTransform[] = $nUserId;
                } elseif (strpos($sOldLogin, '@')) {
                    $aTransform[] = $nUserId;
                }
                $aChangedUsers[$nUserId] = $sUserLogin;
            }

            $sql = "SELECT LOWER(user_login) FROM ?_user WHERE LOWER(user_login) IN (?a)";
            $aEquals = $this->oDb->selectCol($sql, $aChangedUsers);
            if ($aEquals) {
                foreach($aEquals as $sUserLogin) {
                    $nUserId = array_search($sUserLogin, $aChangedUsers);
                    if ($nUserId) {
                        $aChangedUsers[$nUserId] = $aChangedUsers[$nUserId] . '-' . $nUserId;
                        $aTransform[] = $nUserId;
                    }
                }
            }

            $sql = "UPDATE ?_user SET user_login=? WHERE user_id=?d";
            foreach($aChangedUsers as $nUserId => $sUserLogin) {
                $this->oDb->query($sql, $sUserLogin, $nUserId);
                $aChangedUsers[$nUserId] = array(
                    'old_login' => $aUsers[$nUserId],
                    'new_login' => $sUserLogin,
                    'transform' => in_array($nUserId, $aTransform),
                );
            }
        }
        return $aChangedUsers;
    }

    public function BlogReset() {

        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query("TRUNCATE ?_blog");
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        return $xResult;
    }

    public function BlogMigrate($sTableBlog) {

        $sql = "
          INSERT INTO ?_blog (
            `blog_id`,
            `blog_title`,
            `blog_description`,
            `blog_url`,
            `user_owner_id`,
            `blog_type`
            )
          SELECT
            b.`id`,
            b.`name`,
            b.`metadesc`,
            b.`alias`,
            1,
            'open'
          FROM $sTableBlog AS b
        ";
        $xResult = $this->oDb->query($sql);
        if ($xResult !== false) {
            $sql = "SELECT COUNT(*) FROM ?_blog";
            $nCount = $this->oDb->selectCell($sql);
            return array('count' => $nCount);
        }
        return false;
    }


    public function TopicReset() {

        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query("TRUNCATE ?_topic");
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        return $xResult;
    }

    public function TopicMigrate($sTableTopic) {

        $sql = "
          INSERT INTO ?_topic (
            `topic_id`,
            `topic_title`,
            `blog_id`,
            `user_id`,
            `topic_date_add`,
            `topic_date_edit`,
            `topic_url`,
            `topic_publish`
            )
          SELECT
            `id`,
            `title`,
            `catid`,
            `created_by`,
            `publish_up`,
            `modified`,
            `alias`,
            `published`
          FROM $sTableTopic AS t
        ";
        $xResult = $this->oDb->query($sql);
        if ($xResult !== false) {
            $sql = "
              INSERT INTO ?_topic_content (
                `topic_id`,
                `topic_text_short`,
                `topic_text`,
                `topic_text_source`
                )
              SELECT
                `id`,
                `introtext`,
                CASE WHEN (`fulltext` IS NULL OR `fulltext`='') THEN `introtext` ELSE `fulltext` END,
                CASE WHEN (`fulltext` IS NULL OR `fulltext`='') THEN `introtext` ELSE `fulltext` END
              FROM $sTableTopic AS t
            ";
            $xResult2 = $this->oDb->query($sql);

            $sql = "
                UPDATE ?_blog AS b SET blog_count_topic=(
                    SELECT COUNT(*) FROM ?_topic AS t WHERE t.blog_id=b.blog_id
                )
                ";
            $this->oDb->query($sql);

            $sql = "SELECT COUNT(*) FROM ?_topic";
            $nCount = $this->oDb->selectCell($sql);
            return array('count' => $nCount, 'count2' => $xResult2);
        }
        return false;
    }


    public function CommentReset() {

        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query("TRUNCATE ?_comment");
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        return $xResult;
    }

    public function CommentMigrate($sTableComment) {

        $sql = "
          INSERT INTO ?_comment (
            `comment_id`,
            `comment_pid`,
            `target_id`,
            `target_type`,
            `user_id`,
            `comment_text`,
            `comment_text_hash`,
            `comment_user_ip`,
            `comment_date`,
            `comment_publish`,
            `comment_delete`
            )
          SELECT
            `id`,
            `parent`,
            `object_id`,
            'topic',
            `userid`,
            `comment`,
            md5(`comment`),
            `ip`,
            `date`,
            CASE WHEN (`published`=1 AND userid>0) THEN 1 ELSE 0 END,
            CASE WHEN (`published`=0 OR userid=0) THEN 1 ELSE 0 END
          FROM $sTableComment AS b
        ";
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query($sql);
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        if ($xResult !== false) {
            $this->CommentFixImg();
            $sql = "
                UPDATE ?_topic AS t SET topic_count_comment=(
                    SELECT COUNT(*) FROM ?_comment AS c WHERE c.target_id=t.topic_id
                )
                ";
            $this->oDb->query($sql);

            $sql = "SELECT COUNT(*) FROM ?_comment";
            $nCount = $this->oDb->selectCell($sql);
            return array('count' => $nCount);
        }
        return false;
    }

    public function CommentFixImg() {

        $sql="SELECT comment_id, comment_text FROM ?_comment WHERE comment_text LIKE '%[IMG]%'";
        $aComments = $this->oDb->select($sql);
        if ($aComments) {
            foreach ($aComments as $aComment) {
                $sText = preg_replace('/\[img\](.*)\[\/img\]/', '<img src="$1">', $aComment['comment_text']);
                $sHash = md5($sText);
                $sql = "
                    UPDATE ?_comment
                    SET comment_text=?, comment_text_hash=?
                    WHERE comment_id=?d";
                $this->oDb->query($sql, $sText, $sHash, $aComment['comment_id']);
            }
        }
    }

    public function TagReset() {

        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query("TRUNCATE ?_topic_tag");
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        return $xResult;
    }

    public function TagMigrate($sTableTag1, $sTableTag2) {

        $sql = "
          INSERT INTO ?_topic_tag (
            `topic_id`,
            `user_id`,
            `blog_id`,
            `topic_tag_text`
            )
          SELECT tx.`itemID` AS topic_id, t.user_id, t.blog_id, tg.`name`
          FROM  `$sTableTag2` AS tx
          LEFT JOIN ?_topic AS t ON t.topic_id = tx.`itemID`
          LEFT JOIN  `$sTableTag1` AS tg ON tg.`id` = tx.`tagID`
        ";
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query($sql);
        if ($xResult) {
            $sql = "UPDATE ?_topic AS t
            SET t.topic_tags=(
              SELECT GROUP_CONCAT(topic_tag_text SEPARATOR  ',' )
              FROM ?_topic_tag AS tt
              WHERE tt.topic_id=t.topic_id
              GROUP BY tt.topic_id
              ORDER BY tt.topic_tag_id
            )
            ";
            $this->oDb->query($sql);
        }
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        if ($xResult !== false) {
            $sql = "SELECT COUNT(*) FROM ?_topic_tag";
            $nCount = $this->oDb->selectCell($sql);
            return array('count' => $nCount);
        }
        return false;
    }

    public function FriendReset() {

        $this->oDb->query("SET FOREIGN_KEY_CHECKS=0");
        $xResult = $this->oDb->query("TRUNCATE ?_friend");
        $this->oDb->query("SET FOREIGN_KEY_CHECKS=1");
        return $xResult;
    }

    public function FriendMigrate($sTableFriend) {

        $sql = "
            SELECT `userid`, `friends`
            FROM $sTableFriend
            WHERE `friends` IS NOT NULL AND TRIM(`friends`) > ''
        ";
        $aData = $this->oDb->select($sql);
        if ($aData) {
            foreach ($aData as $aRow) {
                $iUserId = $aRow['userid'];
                $aFriends = F::Array_Str2Array($aRow['friends']);
                $aValues = array();
                foreach ($aFriends as $iFriendId) {
                    $aValues[] = array(
                        'user_from' => $iUserId,
                        'user_to' => $iFriendId,
                        'status_from' => 1,
                        'status_to' => 2,
                    );
                }
                $sql = "
                    INSERT IGNORE INTO ?_friend (?#) VALUES(?a)
                ";
                $this->oDb->select($sql, array_keys($aValues[0]), $aValues);
            }
        }
        $sql = "SELECT COUNT(*) FROM ?_friend";
        $nCount = $this->oDb->selectCell($sql);
        return array('count' => $nCount);
    }

}

// EOF