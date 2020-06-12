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
        //deleting the pdf
        try {
            $directory = $this->param->get('public_directory') . DIRECTORY_SEPARATOR . Template::PDFSTORAGE . DIRECTORY_SEPARATOR;
            $output->writeln($directory);
            //get 2days before date
            $rmdir = Template::PDFSTORAGE . date("Y_m_d", strtotime('-2 days'));
            $output->writeln($directory . $rmdir);
            $status = UtilsPdfHelper::deleteDirctory($directory . $rmdir);
            if ($status)
               $output->writeln($rmdir . " deleted successfully");
            else
                $output->writeln( " not exist");
        }
        catch (\Exception $exception){
            $output->writeln($exception->getMessage());
        }
        die(1);

    }

}