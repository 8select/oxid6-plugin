<?php

namespace ASign\EightSelect\Core;

/**
 * Class Request
 * @package ASign\EightSelect\Core
 */
class Request
{
    /**
     * @var string command line arguments
     */
    const ARGUMENTS = 'e:s:';

    const ARGUMENT_METHOD = 'e';
    const ARGUMENT_SHOP_ID = 's';

    protected $aArguments = [];

    /**
     *
     * @param  array $aArguments
     * @throws BadMethodCallException
     */
    public function __construct($aArguments = null)
    {
        $this->initArguments($aArguments);
        if ($this->getArgument(self::ARGUMENT_METHOD) === null) {
            $this->showUsage();

            throw new \BadMethodCallException('Missing required argument -' . self::ARGUMENT_METHOD);
        } elseif ($this->getArgument(self::ARGUMENT_SHOP_ID) === null) {
            $this->showUsage();

            throw new \BadMethodCallException('Missing required argument -' . self::ARGUMENT_SHOP_ID);
        }
    }

    /**
     * return cli argument
     *
     * @param   string $sArgument argument to retrieve
     * @return  string|null argument value or null
     */
    public function getArgument($sArgument)
    {
        return isset($this->aArguments[$sArgument]) ? $this->aArguments[$sArgument] : null;
    }

    /**
     * initializue the arguments from cli
     *
     * @param  array $aArguments
     * @return void
     */
    protected function initArguments($aArguments = null)
    {
        if ($aArguments !== null) {
            $this->aArguments = $aArguments;

            return;
        }

        $this->aArguments = getopt(self::ARGUMENTS);
    }

    /**
     * show usage
     *
     * @return void
     */
    public function showUsage()
    {
        echo <<<EOT
Usage: php bin/eightselect_cron [arguments]

Arguments:
 -e=Command Name
 -s=Shop ID

Command Names:
 -e=export_full
 -e=export_update
 -e=export_upload_full
 -e=export_upload_update
 -e=upload_full
 -e=upload_update
 
Example: php bin/eightselect_cron -e=export_upload_full -s=1

EOT;
    }
}
