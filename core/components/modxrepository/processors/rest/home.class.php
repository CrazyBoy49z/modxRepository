<?php
/*
 * Главная страница. Выводит список новых и лучших пакетов
 */
require_once  dirname(__FILE__).'/response.class.php';

class modxRepositoryHome extends modxRepositoryResponse{
    
    var $root = '<home/>';
    
    public function process(){  
        $data = $this->getData();
         
        return $this->toXML($data);
    }
    
    public function getData(){
        $result = array( );
        
        $url = $this->modx->getOption('site_url', null); 
        $url  .= $this->modx->getOption('modxRepository.request_path', null).'package/';
        $result['url'] = $url;
        if (!isset($_POST)){$_POST = array();}
        if (!isset($_GET)){$_GET = array();}
        $arr = array_merge($_POST,$_GET);
        $repDel = array(255,258,262,271);

        // Получаем новейшие пакеты
        if($newest = $this->getNewest()){
            
            foreach($newest as $n){
                // $this->modx->log(1, print_r($n, 1)); 
                if (in_array($n['parent'], $repDel)){
                    // $this->modx->log(1, print_r($arr, 1)); 
                    if ($arr['api_key']!='alls'){
                        continue;
                        
                    }
                }
                $package = $this->preparePackageRow($n);
                $result[] = array(
                    'newest' => array(
                        'id' => $package['id'],
                        'name' => "{$package['name']} {$package['version']}-{$package['vrelease']} ",
                        'package_name' => $package['name'],
                        'releasedon' => $package['releasedon'],
                    ),
                );
            }
        }
        
        
        // Получаем самые популярные
        if($popular = $this->getPopular()){
            
            foreach($popular as $n){
                // $this->modx->log(1, print_r($n, 1)); 
                if (in_array($n['parent'], $repDel)){
                    // $this->modx->log(1, print_r($arr, 1)); 
                    if ($arr['api_key']!='alls'){
                        continue;
                        
                    }
                }
                $package = $this->preparePackageRow($n);
                
                $result[] = array(
                    'topdownloaded' => array(
                        'id' => $package['id'],
                        'name' => $package['name'],
                        'downloads' => $package['downloads'],
                    ),
                );
                // $result['downloads'] = $package['downloads']+1;
            }
        }
        
        // Получаем самые популярные
        if($all = $this->getAll()){
            foreach($all as $n){
                $package = $this->preparePackageRow($n);
                $result['downloads'] = $result['downloads']+$package['downloads'];
            }
        }
        
        
        if(!empty($all)) {
            $result['packages'] = count($all);
            unset($all,$package);
        }
        
        //$result['packages'] = modxRepositoryRepository::$total;
        return $result;
    }
    
    function getNewest(){ 
        $response = $this->runProcessor('package/getpackages', array(
            'where' => array(
            ),
            'sort'  => array('releasedon, DESC'),
            'group' => array('package_id'),
            'limit' => 10,
            'root'  => $this->getProperty('handler_doc_id'),
        ));
        
        if($result = $response->getResponse()){
            foreach($result as $r){
                $updates[] = array(
                    'package' => $this->preparePackageRow($r),
                );
            }
        }
        
        return $result;
    }
    
    function getPopular(){
        $response = $this->runProcessor('package/getpackages', array(
            'where' => array(
            ),
            'sort'  => array('downloads, DESC'),
            'group' => array('package_id'),
            'limit' => 10,
            'root'  => $this->getProperty('handler_doc_id'),
        ));
        
        if($result = $response->getResponse()){
            foreach($result as $r){
                $updates[] = array(
                    'package' => $this->preparePackageRow($r),
                );
            }
        }
        
        return $result;
    }
    
    function getALL(){
        $response = $this->runProcessor('package/getpackages', array(
            'where' => array(
            ),
            'sort'  => array('downloads, DESC'),
            'group' => array('package_id'),
            'limit' => 0,
            'root'  => $this->getProperty('handler_doc_id'),
        ));
        
        $result = $response->getResponse();
            
        
        return $result;
    }
    function preparePackageRow($data){
        $response = $this->runProcessor('package/preparerow',  $data);
        return $response->getResponse();
    }
}
return 'modxRepositoryHome';
?>
