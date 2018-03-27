<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/26/18
 * Time: 9:45 PM
 */

namespace ConfigManager\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{

    /**
     * @var string
     */
    private $remoteApp = 'https://tom.b-pack.com/svn/appli/c/core_us/trunk/version.inc';

    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Export diff file from DCP configs')
            ->setHelp('This command produce a modelizations diff between remote server config (SVN) and your local app');
        $this->addOption('svn-username', 'u', InputOption::VALUE_REQUIRED, 'Svn username to DCP repo');
        $this->addOption('svn-password', 'p', InputOption::VALUE_REQUIRED, 'Svn password to DCP repo');
        $this->addOption('local', 'l', InputOption::VALUE_REQUIRED, 'The absolute path to version.inc of local application');
        $this->addOption('remote', 'r', InputOption::VALUE_OPTIONAL, 'The url to svn repo with compared app');
        $this->addOption('out', 'o', InputOption::VALUE_OPTIONAL, 'Path to output file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFile = $input->getOption('local');
        if ($remoteApp = $input->getOption('remote')) {
            $this->remoteApp = $remoteApp;
        }
        try {
            if (!is_file($localFile)) {
                throw new \Exception("File path $localFile is incorrect. Specify correct local path to version.inc");
            }
            $output->writeln("<info>Analysing local file $localFile</info>");
            $localConstants = $this->getConstFromText(file_get_contents($localFile));

            $output->writeln("<info>Download remote version.inc: {$this->remoteApp}</info>");
            $content = $this->readRemoteConfig($input->getOption('svn-username'), $input->getOption('svn-password'));
            $output->writeln("<info>Download successful</info>");

            $output->writeln("<info>Analysing remote file... </info>");
            $remoteConstants = $this->getConstFromText($content);

            $output->writeln("<info>Measuring diff and prepare output...</info>");
            $diff = array_diff_assoc($localConstants, $remoteConstants);
            $diffString = $this->formatOutput($diff);
            if ($outFile = $input->getOption('out')) {
                if (false === file_put_contents($outFile, $diffString)) {
                    throw new \Exception("File $outFile is not writable. Please check permissions and file path");
                }
            } else {
                $output->writeln($diffString);
            }
        } catch(\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            exit(1);
        }
    }

    /**
     * @param $usr
     * @param $pass
     * @return mixed
     * @throws \Exception
     */
    protected function readRemoteConfig($usr, $pass) : string
    {
        $ch = curl_init($this->remoteApp);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, "$usr:$pass");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if (!empty($err)) {
            throw new \Exception($err);
        }
        return $data;
    }


    /**
     * Parse string and return all the constants as key value pairs
     * @param string $test
     * @return array
     * @throws \Exception
     */
    private function getConstFromText(string $test) : array
    {
        $regex = '/(?:\b|^)define\s*\((?<constname>[^,]+),(?<constval>[^\)]+)/im';
        preg_match_all('/(?:\b|^)define\s*\((?<constname>[^,]+),(?<constval>[^\)]+)/im', $test, $matches);
        if (empty($matches)) {
            throw new \Exception("Cannot parse file please  verify '$regex'' against your version file");
        }
        $result = [];
        for ($i = 0, $len = count($matches['constname']); $i < $len; ++$i) {
            $result[$this->escape($matches['constname'][$i])] = $this->escape($matches['constval'][$i]);
        }
        return $result;
    }

    /**
     * Escape a string removing quotes
     * @param $str
     * @return string
     */
    private function escape($str) : string {
        return trim($str, '"\'');
    }

    /**
     * @param array $diff
     * @return string
     */
    private function formatOutput(array $diff) : string
    {
        $result = '';
        foreach ($diff as $name => $value) {
            $result .= is_numeric($value) || preg_match('/0x[\da-fA-F]/', $value) ?
                sprintf('define(\'%s\', %s);', $name, $value) :
                sprintf('define(\'%s\', \'%s\');',  $name, $value);
            $result .= PHP_EOL;
        }
        return <<<WRAP
<?php
$result
?>
WRAP;
    }
}