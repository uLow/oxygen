<!DOCTYPE html>
<html>
<?
use oxygen\controller\Controller;

try {
        $body = $this->get_main_entry_view();
        $less = $assets->less->compile();
        $js   = $assets->js->compile();
    } catch (Exception $ex) {
        $body = $this->scope->__wrapException($ex)->get_view();
    }
?>
<head>
<?=$this->put_html5shim()?>
<link rel="stylesheet" type="text/css" href="<?=$this->scope->lib->url('css/redmond/ui.css')?>"/>
<script src="<?=$this->scope->lib->url('js/oxygen.js')?>"></script>
<script src="<?=$this->scope->lib->url('js/jquery-ui-1.8.20.custom.min.js')?>"></script>
<script src="<?=$this->scope->lib->url('js/html5.js')?>"></script>
<?$this->put_stylesheets()?>
<?$this->scope->assets->css->put_view()?>
<?$this->put_javascripts()?>
<?if($this instanceof Controller):?>
<?$current=$this->getCurrent()?>
<link rel="shortcut icon" href="<?=$current->getIconSource()?>"/>
<title><?$current->put_title()?></title>
<?endif?>
<script>
	window.$ = window.oxygen.$;
	window.jQuery = window.oxygen.$;
</script>
</head>
<body>
<?=$body?>
<div class="dialog-space" style="width:0px;height:0px;position:absolute;overflow:hidden"></div>
</body>
</html>

