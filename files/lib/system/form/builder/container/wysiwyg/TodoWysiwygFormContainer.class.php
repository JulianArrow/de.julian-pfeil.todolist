<?php

namespace todolist\system\form\builder\container\wysiwyg;

use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;

/**
 * Extends the wysiwygformcontainer to make settingstab available when settingNodes were set
 *
 * @author      Julian Pfeil <https://julian-pfeil.de>
 * @link        https://darkwood.design/store/user-file-list/1298-julian-pfeil/
 * @copyright   2022 Julian Pfeil Websites & Co.
 * @license     Creative Commons <by> <https://creativecommons.org/licenses/by/4.0/legalcode>
 *
 * @package    de.julian-pfeil.todolist
 * @subpackage system.form.builder.container.wysiwyg
 */
class TodoWysiwygFormContainer extends WysiwygFormContainer
{
    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getNodeById($this->wysiwygId . 'SettingsTab')->available(\count($this->settingsNodes));
    }
}
