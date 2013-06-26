<?$crumbs = $this->getPathToCurrent();?>
<?foreach($crumbs as $child){?>
<li><a class="crumb<?if($child->isCurrent){?> active<?}?>" href="<?=$child->go()?>"><?=$child?></a><?if(!$child->isCurrent){?><span class="divider">/</span><?}?></li>
<?}/*?>
<li><a class="crumb<?if($this->isCurrent){?> active<?}?>" href="<?=$this->go()?>"><?=$this?></a><?if(!$this->isCurrent){?><span class="divider">/</span><?}?></li>

<?if(!$this->isCurrent){?>
    <?foreach($this as $child){?>
    <?if($child->isActive){?>
    <?$child->put_child_crumb()?>
    <?}?>
    <?}?>
<?}*/?>