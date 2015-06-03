<?php

namespace Context;

use Behat\Behat\Exception\PendingException;
use WebDriver\Session;

/**
 * Scripts manager
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScriptsManager
{
    /** @var FeatureContext */
    protected $featureContext;

    /**
     * @param FeatureContext $featureContext
     */
    public function __construct(FeatureContext $featureContext)
    {
        $this->featureContext = $featureContext;
    }

    /**
     * @return Session
     */
    protected function getWebDriverSession()
    {
        return $this->featureContext->getSession()
            ->getDriver()
            ->getWebDriverSession();
    }

    /**
     * Execute javascript
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws PendingException
     */
    public function execute($name)
    {
        $args     = array_slice(func_get_args(), 1);
        $filepath = sprintf('%s/scripts/%s.js', __DIR__, $name);

        if (!file_exists($filepath)) {
            throw new PendingException(
                sprintf(
                    'The file could not be found, you can create it in path "%s"',
                    $filepath
                )
            );
        }

        $script = file_get_contents($filepath);

        $result = $this
            ->getWebDriverSession()
            ->execute(
                [
                    'script' => 'return ('.$script.').apply(null, arguments);',
                    'args'   => $args
                ]
            );

//        var_dump($result);

        return $result;
    }
}
