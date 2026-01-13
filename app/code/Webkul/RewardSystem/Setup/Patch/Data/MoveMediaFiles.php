<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MoveMediaFiles implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;
    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Module\Dir\Reader $reader
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->reader = $reader;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->processDefaultImages();
    }

    /**
     * Copy Banner and Icon Images to Media
     */
    private function processDefaultImages()
    {
        $error = false;
        try {
            $this->createDirectories();
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $ds = "/";
            $baseModulePath = $this->reader->getModuleDir('', 'Webkul_RewardSystem');
            $mediaDetails = [
                "rewardsystem/page" => [
                    "view/base/web/images/rewardsystem/page" => [
                        "reward-page-banner.png",
                        "reward-page-rectangle.png",
                        "reward-page-flow.png"
                    ]
                ]
            ];

            foreach ($mediaDetails as $mediaDirectory => $imageDetails) {
                foreach ($imageDetails as $modulePath => $images) {
                    foreach ($images as $image) {
                        $path = $directory->getAbsolutePath($mediaDirectory);
                        $mediaFilePath = $path.$ds.$image;
                        $moduleFilePath = $baseModulePath.$ds.$modulePath.$ds.$image;

                        if ($this->file->fileExists($mediaFilePath)) {
                            continue;
                        }

                        if (!$this->file->fileExists($moduleFilePath)) {
                            continue;
                        }

                        $this->file->cp($moduleFilePath, $mediaFilePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $error = true;
        }
    }

    /**
     * Create default directories
     */
    private function createDirectories()
    {
        $mediaDirectories = ['rewardsystem/page'];
        foreach ($mediaDirectories as $mediaDirectory) {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $path = $directory->getAbsolutePath($mediaDirectory);
            if (!$this->file->fileExists($path)) {
                $this->file->mkdir($path, 0777, true);
            }
        }
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get dependencies
     */
    public static function getDependencies()
    {
        return [];
    }
}
