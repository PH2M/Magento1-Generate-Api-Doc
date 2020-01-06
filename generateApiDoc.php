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
    protected $eavAttributes = [];
    /**
     * Run script
     *
     */
    public function run()
    {
        $this->doc = '<?php' . PHP_EOL;

        $this->retrieveResources();
        $this->prepareEavAttributes();
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

    protected function prepareEavAttributes()
    {
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection');
        $attributeTypeCollection = Mage::getResourceModel('eav/entity_type_collection')->toArray(['entity_type_id', 'entity_model']);
        $attributeTypeCollection = $attributeTypeCollection['items'];
        $attributeTypes = [];
        foreach ($attributeTypeCollection as $attributeType) {
            $attributeTypes[$attributeType['entity_type_id']] = $attributeType['entity_model'];

        }
        foreach ($attributes as $attribute) {
            $attributeModel = $attributeTypes[$attribute->getEntityTypeId()];
            $this->eavAttributes[$attributeModel][$attribute->getAttributeCode()] = ['type' => $this->convertBackendTypeToApiType($attribute->getBackendType()), 'label' => $attribute->getFrontendLabel()];
        }
    }

    protected function convertBackendTypeToApiType($backendType) {
        $convert = [
            'text' => 'String',
            'varchar' => 'String',
            'int' => 'Number',
            'decimal' => 'Number',
            'datetime' => 'Number',
            'static' => 'String'
        ];
        return $convert[$backendType];
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
        $title = $resource->title . ' ' . $route['action_type'];
        $this->doc .= '/**' . PHP_EOL;
        $this->doc .= ' * @api {' . $requestType . '} /' . trim( $route['route'], '/' ) . ' ' . $title . PHP_EOL;
        foreach ($permissions as $permission) {
            $this->doc .= ' * @apiPermission ' . $permission . PHP_EOL;
        }
        $this->doc .= ' * @apiName ' . $resource->title->asArray() . ' ' . $route['action_type'] . PHP_EOL;
        $this->doc .= ' * @apiGroup ' . $group->asArray() . PHP_EOL;
        if ($resource->attributes) {
            $paramOrSuccess = 'apiParam';
            if ($requestType == 'get') {
                $paramOrSuccess = 'apiSuccess';
            }
            foreach ($resource->attributes->asArray() as $id => $attributes) {
                if ($id == '@' || !is_string($id)) {
                    continue;
                }
                $attributeDetails = $this->retrieveAttributeDetails($id, $resource);
                $this->doc .= ' * @' . $paramOrSuccess . ' ' . $attributeDetails . PHP_EOL;
            }

        }
        $this->doc .= '**/' . PHP_EOL;
    }

    protected function retrieveAttributeDetails($id, $resource)
    {
        $attributeDetails = '{String} ' . $id;
        if($resource->working_model) {
            $model = $resource->working_model->asArray();
            if (array_key_exists($model, $this->eavAttributes) && array_key_exists($id, $this->eavAttributes[$model])) {
                $attributeDetails = '{' .$this->eavAttributes[$model][$id]['type'] .'} ' . $id . ' ' . $this->eavAttributes[$model][$id]['label'];
            }
        }
        return $attributeDetails;
    }
}

$shell = new Mage_Shell_GenerateApiDoc();
$shell->run();
