<?php

/**
 * The ProjectResourcePacker packs an compresses js and css scripts.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/Filter
 */
class ProjectResourcePacker
{
    protected $config;

    protected $modules;

    protected static $types = array(
        'scripts' => array(
            'extension'=> 'js'
        ),
        'styles' => array(
            'extension' => 'css'
        )
    );

    public function __construct(array $modules, $outputType, ProjectResourceFilterConfig $config)
    {
        $this->config = $config;
        $this->modules = $modules;
        $this->outputType = $outputType;
    }

    public static function sortedGlob($from, $glob = '*')
    {
        $files = glob($from.DIRECTORY_SEPARATOR.$glob);
        $sortedFiles = array();
        $indexFile = $from.DIRECTORY_SEPARATOR.'.index';

        if (file_exists($indexFile))
        {
            foreach (file($indexFile) as $fileName)
            {
                $filePath = $from.DIRECTORY_SEPARATOR.trim($fileName);
                if (in_array($filePath, $files))
                {
                    $sortedFiles[] = $filePath;
                }
            }
        }
        else
        {
            $sortedFiles = $files;
        }
        
        $filesToLoad = array();
        foreach ($sortedFiles as $file)
        {
            if (is_dir($file))
            {
                $filesToLoad = array_merge(
                    $filesToLoad,
                    self::sortedGlob($file, $glob)
                );
            }
            else
            {
                $filesToLoad[] = $file;
            }
        }

        return $filesToLoad;
    }

    public function pack()
    {
        foreach($this->modules[$this->outputType] as $module)
        {
            $sourceDir = $this->getResourceDirectoryForModule($module);
            $targetDir = $this->config->getCacheDir() . DIRECTORY_SEPARATOR . $module;
            
            $this->moveResources($sourceDir, $targetDir);
        }

        $this->moveResources(
            AgaviConfig::get('core.app_dir').DIRECTORY_SEPARATOR.'resources',
            $this->config->getCacheDir().DIRECTORY_SEPARATOR.'_global'
        );
    }

    protected function moveResources($sourceDir, $targetDir)
    {
        $directories = array('scripts', 'styles', 'binaries');
        foreach ($directories as $directory)
        {
            if (! is_dir($sourceDir))
            {
                throw new Exception($sourceDir . ' is not a directory. Please move the file into on of the resource subdirectories.');
            }

            $sDir = $sourceDir.DIRECTORY_SEPARATOR.$directory;
            $tDir = $targetDir.DIRECTORY_SEPARATOR.$directory;

            if (! $this->config->isPackingEnabled())
            {
                $this->copyResources($sDir, $tDir);
                continue;
            }

            switch ($directory)
            {
                case 'scripts':
                    $this->packScripts($sDir, $tDir);
                    break;
                case 'styles':
                    $this->packStyles($sDir, $tDir);
                    break;
                default:
                    $this->copyResources($sDir, $tDir);
            }
        }

    }

    protected function getFileExtension($type)
    {
        if (! array_key_exists($type, static::$types))
        {
            throw new Exception('Unknown MIME type: ' . $type);
        }

        return static::$types[$type]['extension'];
    }

    protected function getResourceDirectoryForModule($module)
    {
        $resourcesDir = AgaviConfig::get('core.modules_dir')
            . DIRECTORY_SEPARATOR
            . $module
            . DIRECTORY_SEPARATOR
            . 'resources';

        return $resourcesDir;
    }

    protected function checkForOutdatedPacking($sourceFiles, $targetFile)
    {
        if(file_exists($targetFile))
        {
            $targetMTime = filemtime($targetFile);

            foreach($sourceFiles as $sourceFile)
            {
                if (filemtime($sourceFile) > $targetMTime)
                {
                    return true; //refreshed file found!
                }
            }

            return false; //source files are not newer than target
        }

        return true; //target doesn't exist so it's considered outdated
    }

    protected function packScripts($from, $to)
    {
        $uglifyPath = str_replace('/', DIRECTORY_SEPARATOR, AgaviConfig::get('core.app_dir').'/../node_modules/.bin/uglifyjs');
        $scriptFiles = self::sortedGlob($from);
        $outputPath = $to . DIRECTORY_SEPARATOR . 'combined.js';
        
        if (! $this->checkForOutdatedPacking($scriptFiles, $outputPath))
        {
            return;
        }

        $scripts = array();

        foreach ($scriptFiles as $file)
        {
            $scripts[$file] = shell_exec($uglifyPath.' '.$file);
        }

        $this->ensureDirectoryExists($to);
        file_put_contents($outputPath, $this->concatParts($scripts));
    }

    protected function packStyles($from, $to)
    {
        $lesscPath = str_replace('/', DIRECTORY_SEPARATOR, AgaviConfig::get('core.app_dir').'/../node_modules/.bin/lessc');
        $styleFiles = self::sortedGlob($from);
        $outputPath = $to . DIRECTORY_SEPARATOR . 'combined.css';
        
        if (! $this->checkForOutdatedPacking($styleFiles, $outputPath))
        {
            return;
        }

        $styles = array();

        foreach ($styleFiles as $file)
        {
            if (preg_match('#.less$#', $file))
            {
                if (preg_match('#\.import\.less$#', $file))
                {
                    continue;
                }
                $fileContents = shell_exec($lesscPath.' '.$file);
            }
            else
            {
                $fileContents = file_get_contents($file);
            }
            $styles[$file] = $fileContents;
        }

        $this->ensureDirectoryExists($to);
        file_put_contents($outputPath, $this->concatParts($styles));
    }

    protected function copyResources($from, $to)
    {
        $this->ensureDirectoryExists($to);
        $files = self::sortedGlob($from);
        foreach($files as $file)
        {
            $targetPath = str_replace($from, $to, $file);
            $this->recursiveCopy($file, $targetPath);

            $indexFile = dirname($file).DIRECTORY_SEPARATOR.'.index';
            if (file_exists($indexFile))
            {
                $this->recursiveCopy($indexFile, str_replace($from, $to, $indexFile));
            }
        }

        $indexFile = $from.DIRECTORY_SEPARATOR.'.index';
        if (file_exists($indexFile))
        {
            copy($indexFile, $to.DIRECTORY_SEPARATOR.'.index');
        }
    }

    protected function recursiveCopy($from, $to)
    {
        if(! is_dir($from) )
        {
            if (file_exists($to) && filemtime($from) > filemtime($to))
            {
                return true;
            }

            $this->ensureDirectoryExists(dirname($to));

            return copy($from, $to);
        }

        $dir = opendir($from);
        $success = true;

        while($success && $currentFile = readdir($dir))
        {
            if (preg_match("#^\.#", $currentFile))
            {
                continue;
            }

            $success = $this->recursiveCopy($from.DIRECTORY_SEPARATOR.$currentFile, $to.DIRECTORY_SEPARATOR.$currentFile);
        }

        return $success;
        
    }

    protected function concatParts(array $parts)
    {
        $concat = '';

        foreach($parts as $name => $part)
        {
            $concat .= "\n\n/* $name */\n\n" . $part;
        }

        return $concat;
    }

    protected function ensureDirectoryExists($path)
    {
        $success = true;
        if (! is_dir($path))
        {
            $success = mkdir($path, 0777, true);
        }
 
        clearstatcache(true, $path);
        
        return $success;
    }
}
