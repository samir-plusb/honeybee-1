<?php

/**
 * The ProjectScriptPacker packs an compresses js and css scripts.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Deployment
 */
class ProjectScriptPacker
{
    public function pack(array $files, $type, $baseDir = NULL)
    {
        $combined = '';

        foreach ($files as $file)
        {
            $path = ($baseDir) ? ($baseDir . DIRECTORY_SEPARATOR . $file) : $file;
            if (! is_readable($path))
            {
                throw new Exception(
                    "File " . $file . " is not readable. If you tried to provide an url,
                    please notice that packing is only available for local files."
                );
            }

            $combined .= file_get_contents($path) . "\n\n\n";
        }

        return $this->compressScript($combined, $type);
    }

    protected function compressScript($source, $type)
    {
        $outfile = tempnam(sys_get_temp_dir(), 'compress.'.$type);
        $vendorDir = dirname(AgaviConfig::get('core.app_dir')) . DIRECTORY_SEPARATOR . 'vendor';
        $yuiJarPath = $vendorDir . DIRECTORY_SEPARATOR . 'heartsentwined' .
            DIRECTORY_SEPARATOR . 'yuicompressor' . DIRECTORY_SEPARATOR . 'yuicompressor.jar';

        if (! file_exists($yuiJarPath))
        {
            throw new Exception('YUICompressor binary cannot be found in "'.$yuiJarPath.'".');
        }

        $cmd = sprintf(
            'java -Xmx256M -jar %s --charset %s --type %s -o %s',
            $yuiJarPath,
            escapeshellarg('utf-8'),
            $type,
            escapeshellarg($outfile)
        );

        if (FALSE === ($handle = popen($cmd, 'w')))
        {
            throw new Exception(sprintf('Unable to process file %s.', $outfile));
        }

        fwrite($handle, $source);
        pclose($handle);
        $contents = file_get_contents($outfile);
        unlink($outfile);

        return $contents;
    }
}

?>
