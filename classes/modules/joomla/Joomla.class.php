<?php

class PluginMigrator_ModuleJoomla extends Module {

    /** @var  PluginMigrator_ModuleJoomla_MapperJoomla */
    protected $oMapper;

    public function Init() {

        $this->oMapper = Engine::GetMapper(__CLASS__);
    }

    public function UserReset() {

        return $this->oMapper->UserReset();
    }

    public function UserCheck($sTableUser1) {

        return $this->oMapper->UserCheck($sTableUser1);
    }

    public function UserMigrate($sTableUser1, $sTableUser2, $sTableUser3) {

        return $this->oMapper->UserMigrate($sTableUser1, $sTableUser2, $sTableUser3);
    }

    public function BlogReset() {

        return $this->oMapper->BlogReset();
    }

    public function BlogMigrate($sTableBlog) {

        return $this->oMapper->BlogMigrate($sTableBlog);
    }

    public function TopicReset() {

        return $this->oMapper->TopicReset();
    }

    public function TopicMigrate($sTableTopic) {

        return $this->oMapper->TopicMigrate($sTableTopic);
    }

    public function CommentReset() {

        return $this->oMapper->CommentReset();
    }

    public function CommentMigrate($sTableComment) {

        return $this->oMapper->CommentMigrate($sTableComment);
    }

    public function TagReset() {

        return $this->oMapper->TagReset();
    }

    public function TagMigrate($sTableTag1, $sTableTag2) {

        return $this->oMapper->TagMigrate($sTableTag1, $sTableTag2);
    }

    public function FriendReset() {

        return $this->oMapper->FriendReset();
    }

    public function FriendMigrate($sTableFriend) {

        return $this->oMapper->FriendMigrate($sTableFriend);
    }
}

// EOF