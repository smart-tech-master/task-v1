<?php

// For saving time, I ignored more detailed error exception controlling. But I think you can see my skills enough with this code

class Travel
{
    private $endpoint = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels";

    function __construct($endpoint='') {
        if($endpoint) $this->endpoint = $endpoint;
    }

    public function getTravelList() 
    {
        $result = file_get_contents($this->endpoint);
        return json_decode($result, true);
    }
}

class Company
{
    private $endpoint = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies";
    private $company_list = [];
    private $travel_list = [];

    function __construct($endpoint='') {
        if($endpoint) $this->endpoint = $endpoint;
        $this->getCompanyList();
        $this->travel_list = (new Travel())->getTravelList();
    }

    public function getCompanyList() 
    {
        $result = file_get_contents($this->endpoint);
        $this->company_list = json_decode($result, true);
    }

    public function getCostInfo()
    {
        $result = $this->getCompanyListTree($this->company_list);
        return $result;
    }



    public function getCompanyListTree($company_list, $parentId='0')
    {
        $branch = array();
        $cost = 0;
        foreach ($company_list as $company) {
            if ($company['parentId'] == $parentId) {
                $children = $this->getCompanyListTree($company_list, $company['id']);
                if ($children) {
                    $company['children'] = $children;
                }
                $cost += $this->getSumPrice($company['id']);
                $branch[] = [
                    'id' => $company['id'], 
                    'name' => $company['name'], 
                    'cost' => $cost, 'children' => (isset($company['children'])?$company['children']:[])
                ];
            }
        }

        return $branch;
    }

    public function getSumPrice($companyId)
    {
        $sum = 0;
        foreach($this->travel_list as $travel){
            if($travel['companyId'] == $companyId) 
                $sum += $travel['price'];
        }

        return $sum;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        // Enter your code here
        $result = (new Company())->getCostInfo();
        echo json_encode($result);
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();