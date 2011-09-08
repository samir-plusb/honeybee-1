<?php
/**
 * Test the newswire import action
 *
 * @author Tom Anheyer
 * @package Import
 * @subpackage Test
 * @version $Id$
 */


/**
 * @agaviIsolationDefaultContext console
 * @agaviRoutingInput import.newswire
 */
class NewswireActionTest extends AgaviActionTestCase
{
    /**
     * @var string path to generated data import config file
     */
    protected $configFile;

    /**
     * @var string template for data import config file
     */
    protected $configTemplate = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ae:configurations
	xmlns="http://berlinonline.de/schemas/midas/config/import/definition/1.0"
	xmlns:ae="http://agavi.org/agavi/config/global/envelope/1.1">
	<ae:configuration>
		<import class="NewswireDataImport">
			<name>Newswire agency messages</name>
			<description>Imports newswire agency messages.</description>
			<settings>
				<setting name="couchdb_host">%couchdb.import.host%</setting>
				<setting name="couchdb_port">%couchdb.import.port%</setting>
				<setting name="couchdb_database">%couchdb.import.database%</setting>
			</settings>
			<datasource class="NewswireDataSource" record="DpaNitfNewswireDataRecord">
				<name>DPA messages</name>
				<description>Provides access to the DPA messages.</description>
				<settings>
					<setting name="glob">%%GLOB%%</setting>
					<setting name="timestamp_file">%%TIMESTAMP%%</setting>
				</settings>
			</datasource>
		</import>
	</ae:configuration>
</ae:configurations>
EOT;

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $data
     * @param unknown_type $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->contextName = 'console';
        $this->moduleName = 'Import';
        $this->actionName = 'Newswire';

        $baseDir = AgaviConfig::get('core.testing_dir') . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $this->configFile = AgaviConfig::get('core.cache_dir').'/imports/newswire.config.xml';
        AgaviToolkit::mkdir(AgaviConfig::get('core.cache_dir').'/imports', 0775, TRUE);

        $config = str_replace(
            array('%%GLOB%%', '%%TIMESTAMP%%'),
            array($baseDir.'data/import/newswire/dpa-BerlinBrandenburg/*.xml', AgaviConfig::get('core.cache_dir').'/newswire.flowtest.ts'),
            $this->configTemplate);
        file_put_contents($this->configFile, $config);
    }


    /**
     * @agaviRequestMethod write
     */
    public function testPositiveImport()
    {
        $this->runActionWithParameters('write', array('config' => $this->configFile));
        $this->assertValidatedArgument('config');
        $this->assertViewNameEquals('Input');
    }

    /**
     * @agaviIsolationDefaultContext console
     * @agaviRequestMethod write
     */
    public function testMissingParameter()
    {
        $this->runActionWithParameters('write', array());
        $this->assertViewNameEquals('Error');
    }

    /**
     * @agaviRequestMethod write
     */
    public function testFalseParameter()
    {
        $this->runActionWithParameters('write', array('config' => 'dpasdsdfd_test'));
        $this->assertValidatedArgument('config');
        $this->assertViewNameEquals('Error');
    }


    /**
     * run this action
     *
     * @param string $method request method like 'write', 'read'
     * @param array $arguments for the action
     */
    protected function runActionWithParameters($method, array $arguments)
    {
        $this->setRequestMethod($method);
        $this->setArguments(
            $this->createRequestDataHolder(
                array(
                    AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $arguments)));
        $this->runAction();
    }
}

?>