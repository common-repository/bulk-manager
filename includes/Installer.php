<?php

namespace Bulk\Manager;

class Installer
{
    /**
     * Initialize class functions
     *
     * @return void
     */
    public function run()
    {
        $this->add_version();
    }

    /**
     * Store plugin information
     *
     * @return void
     */
    public function add_version()
    {
        $installed = get_option('bulk_manager_installed');

        if (!$installed) {
            update_option('bulk_manager_installed', time());
        }

        update_option('bulk_manager_version', BULK_MANAGER_VERSION);
    }

}
