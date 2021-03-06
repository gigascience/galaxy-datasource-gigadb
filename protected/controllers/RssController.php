<?php

class RssController extends Controller {

	public $title="";
	public $rssLink="http://gigadb.org";
	public $rssDescription="";
	public $rssAbout="http://gigadb.org";

    public $numberOfLatestDataset=10;



	public function actionFeed($id){
		$search=SearchRecord::model()->findByPk($id);
		$ids=$this->search(json_decode($search->query,true));
		$this->displayDataset($ids);
	}

    public function actionLatest(){
        $criteria=new CDbCriteria;
        $criteria->limit = $this->numberOfLatestDataset;
        $datasets = Dataset::model()->findAll($criteria);
        $this->generateFeed($datasets);
    }



	public function displayDataset($ids){

		$criteria = new CDbCriteria();
		$criteria->addInCondition("id", $ids);
		$datasets = Dataset::model()->findAll($criteria);
		$this->generateFeed($datasets);
	}

	private function generateFeed($datasets){
		Yii::import('ext.feed.*');

		// specify feed type
		$feed = new EFeed(EFeed::RSS1);
		$feed->title = $this->title;
		$feed->link = $this->rssLink;
		$feed->description = $this->rssDescription;
		$feed->RSS1ChannelAbout = $this->rssAbout;

		foreach ($datasets as $key => $dataset) {
			// create dataset item
			$item = $feed->createNewItem();
			$item->title = $dataset->title;
			$item->link = Yii::app()->request->hostInfo."/dataset/".$dataset->identifier;
			$item->date = $dataset->publication_date;
			$item->description = $dataset->description;
			$item->addTag('dc:subject', $dataset->title);

			$feed->addItem($item);
		}


		if(count($datasets)==0){
			echo "No Item";
		}else {
			$feed->generateFeed();
		}

	}


	private function newSphinxClient() {
        $s = new SphinxClient;
        $s->setServer(Yii::app()->params['sphinx_servername'], Yii::app()->params['sphinx_port']);
        $s->setMaxQueryTime(5000);
        $s->SetLimits(0,100);
        $s->SetMatchMode(SPH_MATCH_EXTENDED);
        return $s;
    }

    private function search($criteria){
		$s = $this->newSphinxClient();
		$keyword=isset($criteria['keyword'])?$criteria['keyword']:"";

		$dataset_type=isset($criteria['dataset_type'])?$criteria['dataset_type']:"";
        $publisher=isset($criteria['publisher'])?$criteria['publisher']:"";
        $common_name=isset($criteria['common_name'])?$criteria['common_name']:"";
        $project=isset($criteria['project'])?$criteria['project']:"";
        $pubdate_from=isset($criteria['pubdate_from'])?$criteria['pubdate_from']:"";
        $pubdate_to=isset($criteria['pubdate_to'])?$criteria['pubdate_to']:"";
        $moddate_from=isset($criteria['moddate_from'])?$criteria['moddate_from']:"";
        $moddate_to=isset($criteria['moddate_to'])?$criteria['moddate_to']:"";
        $external_link_type=isset($criteria['external_link_type'])?$criteria['external_link_type']:"";
        $pubdate_from=$this->convertDate($pubdate_from);
        $pubdate_to=$this->convertDate($pubdate_to);
        $moddate_from=$this->convertDate($moddate_from);
        $moddate_to=$this->convertDate($moddate_to);



        if(is_array($dataset_type)){
            $s->SetFilter( 'dataset_type_ids', $dataset_type );
        }
        if(is_array($publisher)){

            $s->SetFilter( 'publisher_id', $publisher );
        }

        if(is_array($common_name)){

            $s->SetFilter( 'species_ids', $common_name );
        }

        if(is_array($project)){
            $s->SetFilter( 'project_ids', $project );
        }
        if(is_array($external_link_type)){
            $s->SetFilter( 'external_type_ids', $external_link_type );
        }


        if($pubdate_from && $pubdate_to && $pubdate_to > $pubdate_from){
            $s->SetFilterRange('publication_date',$pubdate_from,$pubdate_to);
        }

        if($moddate_from && $moddate_to && $moddate_to > $moddate_from){
            $s->SetFilterRange('modification_date',$moddate_from,"");
        }





		$result = $s->query($keyword,"dataset");
        $matches=array();
        if(isset($result['matches'])) {
            $matches=$result['matches'];
        }

        $result=array_keys($matches);
        return $result;
	}
	private function convertDate($date){
        return strtotime($date);
    }


}
