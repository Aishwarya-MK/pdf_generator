<?php
/**
 * Created by PhpStorm.
 * User: techjini
 * Date: 12-06-2020
 * Time: 08:47
 */

namespace App\Command;


use App\Entity\Template;
use App\Services\UtilsPdfHelper;
use mysql_xdevapi\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DeletePdfCommand extends Command
{
    protected static $defaultName = 'app:dlete-pdf';

    private $param;
    private  $container;

    protected function configure()
    {
        $this->setDescription("delete pdf folders" );
    }

    public function __construct(ParameterBagInterface $param,ContainerInterface $container)
    {
        $this->param =$param;
        $this->container =$container;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->param->get('project_directory'). DIRECTORY_SEPARATOR. "var". DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."deletefile.log";
        $fileHandler = fopen($file,'a');
        $directory = $this->param->get('public_directory') . DIRECTORY_SEPARATOR . Template::PDFSTORAGE . DIRECTORY_SEPARATOR;
        //remove 2day's old directory
        $rmdir = Template::PDFSTORAGE . date("Y_m_d", strtotime('-2 days'));
        fputs($fileHandler,date("y-m-d")." : started deleting the directory -".$rmdir."\n");
        try {
            $output->writeln($directory . $rmdir);
            $status = UtilsPdfHelper::deleteDirectory($directory . $rmdir);
            if ($status)
                fputs($fileHandler, $rmdir . " deleted successfully\n");
            else
                fputs($fileHandler, $rmdir . " not exist\n");
        }
        catch (\Exception $exception){
            fputs($fileHandler, $exception->getMessage()."\n");
            $output->writeln($exception->getMessage());
        }
        fclose($fileHandler);
        die(1);
    }

}