<?php

namespace app\components;

use app\Component;
/**
 * Class Help
 * @package app\components
 */
class Fileutils extends Component
{

    /**
     * delete all files and subdirectories in $directory
     * @param $directory
     */
    public function clearDirectory($directory)
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            return 0;
        }
        $files = array_diff(scandir($directory), ['.', '..']);
        $count = count($files);
        foreach ($files as $file) {
            if (!is_dir($directory . "/" . $file)) {
                unlink($directory . "/" . $file);
            } else {
                $count += $this->clearDirectory($directory . '/' . $file);
            }
        }
        rmdir($directory);
        return $count;
    }
}