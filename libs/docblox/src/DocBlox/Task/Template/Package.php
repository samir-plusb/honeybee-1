<?php
/**
 * DocBlox
 *
 * PHP Version 5
 *
 * @category  DocBlox
 * @package   Tasks
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docblox-project.org
 */

/**
 * Packages a template for distribution.
 *
 * Packages a template so that it can be distributed by the docblox template
 * repository.
 *
 * This task accepts 2 arguments:
 * * The path to the template source files
 * * The short name for this template; this must be unique in DocBlox' repository.
 *
 * This task depends on PEAR's PEAR_PackageFileManager2 package.
 *
 * @category    DocBlox
 * @package     Tasks
 * @subpackage  Template
 * @author      Mike van Riel <mike.vanriel@naenius.com>
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        http://docblox-project.org
 *
 * @method string getVersion() Version of the template to install
 */
class DocBlox_Task_Template_Package extends DocBlox_Task_Abstract
{
    /** @var string The name of this task including namespace */
    protected $taskname = 'template:package';

    /**
     * Configures the parameters which this task accepts.
     *
     * @return void
     */
    protected function configure()
    {
        $this->addOption(
            'debug', '',
            'Outputs the package\'s XML rather than generate it.'
        );
    }

    /**
     * Executes the transformation process.
     *
     * @throws Zend_Console_Getopt_Exception
     *
     * @return void
     */
    public function execute()
    {
        error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
        if (!@include('PEAR/PackageFileManager2.php')) {
            throw new Exception(
                'Unable to find the PEAR_PackageFileManager2 package, '
                        . 'is it installed?'
            );
        }
        PEAR::setErrorHandling(PEAR_ERROR_DIE);


        $args = $this->getRemainingArgs();
        if (!isset($args[1])) {
            throw new Exception('Missing path to the template source');
        }
        if (!isset($args[2])) {
            throw new Exception('Missing the template name');
        }

        $path     = $args[1];
        $template = $args[2];

        if (!preg_match('/^[a-z\d][a-z_\d]{2,}$/', $template)) {
            throw new Exception(
                'A template name may only consist of lowercase letters, digits and'
                .' underscores; may not start with an underscore and consist of '
                .'at least 3 characters.'
            );
        }

        if (!file_exists($path . '/template.xml')
            || !is_readable($path . '/template.xml')
        ) {
            throw new Exception(
                'Template definition (' . $path . '/template.xml) was not found '
                . 'or is unreadable.'
            );
        }

        $settings    = simplexml_load_file($path . '/template.xml');
        $version     = (string)$settings->version;
        $author      = (string)$settings->author;
        $description = (string)$settings->description;
        $email       = (string)$settings->email;

        // merge the options with these defaults.
        $options = array(
            'packagefile' => 'package.xml',
            'filelistgenerator' => 'file',
            'simpleoutput' => true,
            'baseinstalldir' => '/DocBlox/data/templates/' . $template,
            'packagedirectory' => $path,
            'clearcontents' => true,
            'ignore' => array(),
            'exceptions' => array(),
            'installexceptions' => array(),
            'dir_roles' => array(
                '/' => 'php', // explicitly set the role to php to allowthe templates to
                // installed inside the DocBlox PEAR folder. This will help keep
                // everything together
            ),
        );

        $packagexml = PEAR_PackageFileManager2::importOptions('', $options);
        $packagexml->setPackageType('php');

        $packagexml->setPackage('DocBlox_Template_' . $template);
        $packagexml->setSummary('The ' . $template . ' template for DocBlox');
        $packagexml->setDescription($description);
        $packagexml->setChannel('pear.docblox-project.org');
        $packagexml->setNotes('Automatically generated by the DocBlox packager');

        $packagexml->setPhpDep('5.2.6');
        $packagexml->setPearinstallerDep('1.4.0');
        $packagexml->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.0');
        $packagexml->addPackageDepWithChannel('required', 'DocBlox', 'pear.docblox-project.org', '0.17.0');

        if ($settings->dependencies->template) {
            foreach ($settings->dependencies->template as $dependency) {
                $dependency_name    = (string)$dependency['name'];
                $dependency_version = (string)$dependency['version'];
                $packagexml->addPackageDepWithChannel(
                    'required',
                        'DocBlox_Template_'. $dependency_name,
                    'pear.docblox-project.org',
                    $dependency_version
                );
            }
        }

        $packagexml->addMaintainer('lead', '', $author, $email);
        $packagexml->setLicense('MIT', 'http://www.opensource.org/licenses/mit-license.html');

        // Add this as a release, and generate XML content
        $packagexml->addRelease();

        $packagexml->setAPIVersion($version);
        $packagexml->setReleaseVersion($version);
        $packagexml->setReleaseStability('stable');
        $packagexml->setAPIStability('stable');

        $packagexml->generateContents();
        if ($this->getDebug()) {
            $packagexml->debugPackageFile();
            return;
        }

        $packagexml->writePackageFile();
        passthru('pear package '.$path.'/package.xml');
    }

}