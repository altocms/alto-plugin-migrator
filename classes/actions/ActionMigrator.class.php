<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginMigrator_ActionMigrator extends ActionPlugin {

    protected $aMigrationData = array();

    public function Init() {

        if (!E::IsAdmin()) {
            //return $this->EventNotFound();
        }

        $this->SetDefaultEvent('index');
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
        $this->AddEvent('joomla15x', 'EventJoomla15x');
    }

    protected function EventIndex() {

        $this->SetTemplateAction('index');
    }

    protected function _initDataJoomla15x($sKey) {

        $aData = (array)Config::Get($sKey);

        $aData['jprefix'] = (!empty($aData['jprefix']) ? $aData['jprefix'] : 'jos_');
        $aData['jusers1'] = (!empty($aData['jusers1']) ? $aData['jusers1'] : '___' . $sKey . '.jprefix___users');
        $aData['jusers2'] = (!empty($aData['jusers2']) ? $aData['jusers2'] : '___' . $sKey . '.jprefix___k2_users');
        $aData['jusers3'] = (!empty($aData['jusers3']) ? $aData['jusers3'] : '___' . $sKey . '.jprefix___community_users');
        $aData['jblogs'] = (!empty($aData['jblogs']) ? $aData['jblogs'] : '___' . $sKey . '.jprefix___k2_categories');
        $aData['jtopics'] = (!empty($aData['jtopics']) ? $aData['jtopics'] : '___' . $sKey . '.jprefix___k2_items');
        $aData['jcomments'] = (!empty($aData['jcomments']) ? $aData['jcomments'] : '___' . $sKey . '.jprefix___jcomments');
        $aData['jtags'] = (!empty($aData['jtags']) ? $aData['jtags'] : '___' . $sKey . '.jprefix___k2_tags');
        $aData['jtags_xref'] = (!empty($aData['jtags_xref']) ? $aData['jtags_xref'] : '___' . $sKey . '.jprefix___k2_tags_xref');
        $aData['jfriends'] = (!empty($aData['jfriends']) ? $aData['jfriends'] : '___' . $sKey . '.jprefix___community_users');

        Config::Set($sKey, $aData);
    }

    protected function _saveDataJoomla15x($sKey, $aData) {

        $aConfig = array();
        foreach($aData as $sConfigKey => $sConfigValue) {
            $aConfig[$sKey . '.' . $sConfigKey] = $sConfigValue;
        }
        Config::WriteCustomConfig($aConfig);
    }

    protected function EventJoomla15x() {

        $sKey = 'migration.joomla15x.data';
        $this->_initDataJoomla15x($sKey);
        E::ModuleViewer()->Assign('sKey', $sKey);

        if (!$this->GetPost('step') || ($this->GetPost('step') == '1')) {
            $this->_eventJoomla15xStep1($sKey);
        } elseif ($this->GetPost('step') == '2') {
            $this->_eventJoomla15xStep2($sKey);
        } elseif ($this->GetPost('step') == '3') {
            $this->_eventJoomla15xStep3($sKey);
        } elseif ($this->GetPost('step') == '4') {
            $this->_eventJoomla15xStep4($sKey);
        } elseif ($this->GetPost('step') == '5') {
            $this->_eventJoomla15xStep5($sKey);
        } elseif ($this->GetPost('step') == '6') {
            $this->_eventJoomla15xStep6($sKey);
        } elseif ($this->GetPost('step') == '7') {
            $this->_eventJoomla15xStep7($sKey);
        } elseif ($this->GetPost('step') > 7) {
            $this->_eventJoomla15xStepFinish($sKey);
        }
    }

    protected function _eventJoomla15xStep1($sKey) {

        if ($this->GetPost('step') == '1') {
            $aData = (array)Config::Get($sKey);
            $aData['jprefix'] = $this->GetPost('jprefix');
            $this->_saveDataJoomla15x($sKey, $aData);
            return $this->_eventJoomla15xStep2($sKey);
        }
        E::ModuleViewer()->Assign('nStep', 1);
        $this->SetTemplateAction('joomla15x-step1');
    }


    /**
     * Users
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep2($sKey) {

        $nStep = 2;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jusers1')) {
                $aData['jusers1'] = $this->GetPost('jusers1');
                $aData['jusers2'] = $this->GetPost('jusers2');
                $aData['jusers3'] = $this->GetPost('jusers3');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_UserReset();
            $aErrUsers = $this->PluginMigrator_ModuleJoomla_UserCheck($aData['jusers1']);
            if ($aErrUsers) {
                E::ModuleViewer()->Assign('aErrUsers', $aErrUsers);
            } else {
                $aResult = $this->PluginMigrator_ModuleJoomla_UserMigrate($aData['jusers1'], $aData['jusers2'], $aData['jusers3']);
                if (!$aResult) {
                    E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
                }
                E::ModuleViewer()->Assign('nUsersCnt', $aResult['count']);
                E::ModuleViewer()->Assign('aUsersChanged', $aResult['changed']);
                E::ModuleViewer()->Assign('nDone', 1);
            }
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStep3($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step2');
    }

    /**
     * Blogs
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep3($sKey) {

        $nStep = 3;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jblogs')) {
                $aData['jblogs'] = $this->GetPost('jblogs');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_BlogReset();
                $aResult = $this->PluginMigrator_ModuleJoomla_BlogMigrate($aData['jblogs']);
                if (!$aResult) {
                    E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
                }
                E::ModuleViewer()->Assign('nBlogsCnt', $aResult['count']);
                E::ModuleViewer()->Assign('nDone', 1);
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStep4($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step3');
    }

    /**
     * Topics
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep4($sKey) {

        $nStep = 4;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jtopics')) {
                $aData['jtopics'] = $this->GetPost('jtopics');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_TopicReset();
            $aResult = $this->PluginMigrator_ModuleJoomla_TopicMigrate($aData['jtopics']);
            if (!$aResult) {
                E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
            }
            E::ModuleViewer()->Assign('nTopicsCnt', $aResult['count']);
            E::ModuleViewer()->Assign('nDone', 1);
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStep5($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step4');
    }

    /**
     * Comments
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep5($sKey) {

        $nStep = 5;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jcomments')) {
                $aData['jcomments'] = $this->GetPost('jcomments');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_CommentReset();
            $aResult = $this->PluginMigrator_ModuleJoomla_CommentMigrate($aData['jcomments']);
            if (!$aResult) {
                E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
            }
            E::ModuleViewer()->Assign('nCommentsCnt', $aResult['count']);
            E::ModuleViewer()->Assign('nDone', 1);
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStep6($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step5');
    }

    /**
     * Tags
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep6($sKey) {

        $nStep = 6;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jtags')) {
                $aData['jtags'] = $this->GetPost('jtags');
                $aData['jtags_xref'] = $this->GetPost('jtags_xref');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_TagReset();
            $aResult = $this->PluginMigrator_ModuleJoomla_TagMigrate($aData['jtags'], $aData['jtags_xref']);
            if (!$aResult) {
                E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
            }
            E::ModuleViewer()->Assign('nTagsCnt', $aResult['count']);
            E::ModuleViewer()->Assign('nDone', 1);
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStep7($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step6');
    }

    /**
     * Friends
     *
     * @param $sKey
     * @return mixed
     */
    protected function _eventJoomla15xStep7($sKey) {

        $nStep = 7;
        if ($this->GetPost('step') == $nStep && !$this->GetPost('done')) {
            $aData = (array)Config::Get($sKey);
            if ($this->IsPost('jfriends')) {
                $aData['jfriends'] = $this->GetPost('jfriends');
                $this->_saveDataJoomla15x($sKey, $aData);
            }
            $this->PluginMigrator_ModuleJoomla_FriendReset();
            $aResult = $this->PluginMigrator_ModuleJoomla_FriendMigrate($aData['jfriends']);
            if (!$aResult) {
                E::ModuleViewer()->Assign('sError', 'Ошибка переноса данных');
            }
            E::ModuleViewer()->Assign('nFriendsCnt', $aResult['count']);
            E::ModuleViewer()->Assign('nDone', 1);
        } elseif ($this->GetPost('step') == $nStep && $this->GetPost('done')) {
            return $this->_eventJoomla15xStepFinish($sKey);
        }
        E::ModuleViewer()->Assign('nStep', $nStep);
        $this->SetTemplateAction('joomla15x-step7');
    }

    protected function _eventJoomla15xStepFinish() {

        $this->SetTemplateAction('joomla15x-step-finish');
    }

}

// EOF