<?php
require_once 'abstract.php';

/**
 *
 * @category    Mage
 * @package     Mage_Shell
 */
class Mage_Shell_ApiDoc extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
       $node = new Varien_Simplexml_Config;
        $config = Mage::getConfig()->loadModulesConfiguration('api2.xml');
        $node->setXml($config->getNode('api2'));
        $resources = $node->getNode('resources')->children();
        $doc = '<?php' . PHP_EOL;

       
        foreach ($resources as $i => $resource) {
            /** @var $resource Mage_Core_Model_Config_Element */
            /** @var $group Mage_Core_Model_Config_Element */
            $group = $resource->group;
            foreach ($resource->routes->asArray() as $route) {
                $doc .= '/**' . PHP_EOL;
                $doc .= ' * @api {get} ' . $route['route'] . PHP_EOL;
                $doc .= ' * @apiName ' . $resource->title->asArray() . ' ' . $route['action_type'] . PHP_EOL;
                $doc .= ' * @apiGroup ' . $group->asArray() . PHP_EOL;
                foreach ($resource->attributes->asArray() as $id => $attributes) {
                    if($id == '@' || !is_string($id)) { continue; }
                    $doc .= ' * @apiParam {String} ' . $id . PHP_EOL;
                }
            $doc .= '**/' . PHP_EOL;
            }
        }
        $doc .= ' ?>';
        file_put_contents('./apidoc.php', $doc);
    }
}

$shell = new Mage_Shell_ApiDoc();
$shell->run();
