<?php
require_once 'abstract.php';

/**
 *
 * @category    Mage
 * @package     Mage_Shell
 */
class Mage_Shell_GenerateApiDoc extends Mage_Shell_Abstract
{

    protected $doc = '';
    protected $resources = '';

    /**
     * Run script
     *
     */
    public function run()
    {
        $this->doc = '<?php' . PHP_EOL;

        $this->retrieveResources();
        $this->createApiDocConfigFile();
        foreach ($this->resources as $i => $resource) {
            $privileges = $this->getResourcePrivilege($resource);
            $group = $resource->group;
            foreach ($privileges as $requestType => $permissions) {
                foreach ($resource->routes->asArray() as $route) {
                    $this->retrieveApiDocRoute($route, $group, $requestType, $permissions, $resource);
                }
            }
        }
        $this->doc .= ' ?>';
        file_put_contents(Mage::getBaseDir() . DS . 'api' . DS . 'input' . DS . 'apidoc.php', $this->doc);
    }

    protected function getResourcePrivilege($resource)
    {
        $resourcePrivilege = [];
        $privileges = $resource->privileges;
        foreach ($privileges->asArray() as $permission => $privilege) {
            if (array_key_exists('retrieve', $privilege)) {
                $resourcePrivilege['get'][] = $permission;
            }
            if (array_key_exists('create', $privilege)) {
                $resourcePrivilege['post'][] = $permission;
            }
            if (array_key_exists('delete', $privilege)) {
                $resourcePrivilege['delete'][] = $permission;
            }
            if (array_key_exists('update', $privilege)) {
                $resourcePrivilege['update'][] = $permission;
            }
        }
        return $resourcePrivilege;
    }

    protected function createApiDocConfigFile()
    {
        $apiDoc = [
            'name' => 'Magento REST API documentation',
            'description' => '',
            'version' => '1.0.0',
            'title' => 'Magento REST API documentation',
            'url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'api/rest',
            'template' => [
                'withCompare' => false,
                'withGenerator' => false
            ]
        ];
        file_put_contents(Mage::getBaseDir() . DS . 'api' . DS . 'input' . DS . 'apidoc.json', json_encode($apiDoc));
    }


    protected function retrieveResources()
    {
        $node = new Varien_Simplexml_Config;
        $config = Mage::getConfig()->loadModulesConfiguration('api2.xml');
        $node->setXml($config->getNode('api2'));
        $this->resources = $node->getNode('resources')->children();
    }

    protected function retrieveApiDocRoute($route, $group, $requestType, $permissions, $resource)
    {
        $this->doc .= '/**' . PHP_EOL;
        $this->doc .= ' * @api {' . $requestType . '} ' . $route['route'] . PHP_EOL;
        foreach ($permissions as $permission) {
            $this->doc .= ' * @apiPermission ' . $permission . PHP_EOL;
        }
        $this->doc .= ' * @apiName ' . $resource->title->asArray() . ' ' . $route['action_type'] . PHP_EOL;
        $this->doc .= ' * @apiGroup ' . $group->asArray() . PHP_EOL;
        if ($resource->attributes) {
            if ($requestType == 'get') {
                foreach ($resource->attributes->asArray() as $id => $attributes) {
                    if ($id == '@' || !is_string($id)) {
                        continue;
                    }
                    $this->doc .= ' * @apiSuccess {String} ' . $id . PHP_EOL;
                }
            } else {
                foreach ($resource->attributes->asArray() as $id => $attributes) {
                    if ($id == '@' || !is_string($id)) {
                        continue;
                    }
                    $this->doc .= ' * @apiParam {String} ' . $id . PHP_EOL;
                }
            }

        }
        $this->doc .= '**/' . PHP_EOL;
    }
}

$shell = new Mage_Shell_GenerateApiDoc();
$shell->run();
