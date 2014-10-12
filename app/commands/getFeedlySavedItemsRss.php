<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class getFeedlySavedItemsRss extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cli:feedly:getSavedItemsRss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        //
        $oauth = $this->option('oauth') ? : \Config::get('core.oauth');
        $userid = $this->option('userid') ? : \Config::get('core.userid');
        $count = $this->option('count') ? : 10;

        $curl = new Curl\Curl();
        $curl->setHeader("Authorization", "OAuth $oauth");
        $curl->setHeader("Content-Type", "application/json");
        $curl->get("http://cloud.feedly.com/v3/streams/contents?streamId=user/$userid/tag/global.saved&count=$count");

        $saved_item = json_decode($curl->response, true);
        //print_r($saved_item['items'][0]);

        $rss_items = array_map(function ($item) {
            return [
                "title" => $item['title'],
                "link" => $item['alternate'][0]['href'],
                "guid" => $item['id'],
                "pubData" => $item['published']
            ];
        }, $saved_item['items']);

        //print_r($rss_items);

        $rss = new Thujohn\Rss\Rss();
        foreach ($rss_items as $item) {
            $rss->item($item);
        }
        echo $rss->render()->asXML();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('oauth', null, InputOption::VALUE_OPTIONAL, 'feedly Cloud API OAuth.', null),
            array('userid', null, InputOption::VALUE_OPTIONAL, 'feedly Cloud API User ID.', null),
            array('count', null, InputOption::VALUE_OPTIONAL, 'number of item returned.', null),
        );
    }

}
