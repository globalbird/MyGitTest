<?php

namespace App\Libraries;

use App\Models\Setting;

class AppViewComposer
{
    public function compose($view)
    {
        $settingsModel = new Setting();
        $settings = $settingsModel->find(1); // Assuming the settings are stored in a single row

        // Pass logo and favicon URLs to the view
        $view->setVar('logo', $settings->blog_logo);
        $view->setVar('favicon', $settings->blog_favicon);
    }
}