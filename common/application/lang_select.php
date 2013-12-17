<?o('div[style="float: right; margin-top: 12px; margin-right: 12px;"]')?>
<?foreach($this->language->languages as $k=>$v){?>
<a href="javascript:void(0)" class="selectLang <?=$k?><?=($this->getLang()==$k)?" active":""?>" data-lang="<?=$k?>"></a>
<?}?>