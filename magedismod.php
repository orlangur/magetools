#!/usr/bin/env php
<?php
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'abstract' . DIRECTORY_SEPARATOR . 'modules.abstract.php';

class Magetools_DisableModule extends Magetools_Modules_Abstract
{
    protected $_scriptName = 'magedismod.php';

    protected function _process()
    {
        $force = $this->_getOpt('force');

        if (!$force && $this->_moduleStatus === false) {
            throw new Exception(sprintf('This module "%s" is already disabled', $this->_moduleName));
        }

        $dependsList = array();
        $this->_findDependsFromModule($this->_moduleName, $dependsList);

        if (count($dependsList)) {
            $enabled = array_filter($dependsList, function ($var) {
                return $var === true;
            });

            $this->_printMessage(sprintf(
                'Cannot disable this module, because following modules "%s" is(are) depends from this',
                implode('", "', array_keys($enabled))
            ));
            $this->_printMessage('Do you want to disable them together? [Y/n]');

            $line = trim(fgets(STDIN));
            if (in_array($line, array('y', 'Y'))) {
                foreach ($enabled as $_moduleName => $status) {
                    if ($this->_changeModuleStatus($_moduleName, 'false')) {
                        $this->_printMessage(sprintf('Module "%s" was disabled successfully', $_moduleName));
                    } else {
                        $this->_printMessage(sprintf('Module "%s" wasn\'t disabled', $_moduleName));
                    }
                }
            }
        }

        $this->_moduleXml->modules->{$this->_moduleName}->active = 'false';

        if ($this->_moduleXml->saveXML($this->_modulePath)) {
            $this->_printMessage(sprintf('Module "%s" was disabled successfully', $this->_moduleName));
        } else {
            $this->_printMessage(sprintf('Module "%s" wasn\'t disabled', $this->_moduleName));
        }
    }
}

if (!defined('DO_NOT_RUN')) {
    $run = new Magetools_DisableModule();
    $run->run();
    exit(0);
}
