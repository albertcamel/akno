<?php
/**
 * Created by PhpStorm.
 * User: benny
 * Date: 04/11/2016
 * Time: 10:00
 */
namespace Camelweb\Debug\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;

class FlushStaticFilesCommand extends Command{
    const INPUT_KEY_EXTENDED = 'extended';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    protected $filesystem;

    public function __construct(
        ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        ObjectManagerInterface $manager,
        Filesystem $filesystem,
        State $state
    ){
        //$state->setAreaCode('admin');
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->objectManager = $manager;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                '--css',
                null,
                InputOption::VALUE_NONE,
                'Clean only CSS static files'
            )
        ];
        $this->setName('cache:flush-static-files')
            ->setDescription('Flush static files')
            ->setDefinition($options);
        parent::configure();
    }

    private function emptyDir($code, $subPath = null)
    {
        $messages = [];

        $dir = $this->filesystem->getDirectoryWrite($code);
        $dirPath = $dir->getAbsolutePath();
        if (!$dir->isExist()) {
            $messages[] = "The directory '{$dirPath}' doesn't exist - skipping cleanup";
            return $messages;
        }
        foreach ($dir->search('*', $subPath) as $path) {
            if ($path !== '.' && $path !== '..') {
                $messages[] = $dirPath . $path;
                try {
                    $dir->delete($path);
                } catch (FilesystemException $e) {
                    $messages[] = $e->getMessage();
                }
            }
        }

        return $messages;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        /*$output->writeln('<error>' . 'writeln surrounded by error tag' . '</error>');
        $output->writeln('<comment>' . 'writeln surrounded by comment tag' . '</comment>');
        $output->writeln('<info>' . 'writeln surrounded by info tag' . '</info>');
        $output->writeln('<question>' . 'writeln surrounded by questiontag' . '</question>');
        $output->writeln('writeln with normal output');
        if ($input->getOption(self::INPUT_KEY_EXTENDED)) {
            $output->writeln('');
            $output->writeln('<info>'.'Extended parameter is given'.'</info>');
        }
        $output->writeln('');*/

        /** @var \Magento\Framework\App\State\CleanupFiles $objCleanupFiles */
        $objCleanupFiles=$this->objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $css = $input->getOption('css');

        if (!empty($css)) {
            $messages=$this->emptyDir(DirectoryList::STATIC_VIEW,'frontend/Muntz/ambiant/en_US/css');
            $output->writeln('<info>' . print_r($messages) . '</info>');
            $output->writeln('<info>' . 'The static css files has been flushed.' . '</info>');
        }else{
            $objCleanupFiles->clearMaterializedViewFiles();
            $output->writeln('<info>' . 'The static files has been flushed.' . '</info>');
        }
    }
}