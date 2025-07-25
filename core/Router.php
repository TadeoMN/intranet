<?php
namespace Core;
final class Router{
    private array $r=[];
    public function get($u,$a){$this->r['GET'][$u]=$a;}
    public function post($u,$a){$this->r['POST'][$u]=$a;}
    public function dispatch($m,$u){
        $u=rtrim(explode('?',$u)[0],'/')?:'/';
        $a=$this->r[$m][$u]??null;
        if(!$a){http_response_code(404);exit('404');}
        [$c,$f]=explode('@',$a);
        $o="\\App\\Controllers\\$c"; echo (new $o)->{$f}();
    }
}