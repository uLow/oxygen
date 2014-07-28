<?$page_count = ceil(count($this->model)/$this->sliceSize)?>
<?$pageLimit=7?>
<?if($this->paginatorPage>1){?>
<a class="paginatorPage" href="<?=$this->go(array("page"=>1))?>"><img src="<?=$this->scope->assets->getIcon('resultset_first','png')?>"></a>
<?}?>
<?if($this->paginatorPage-1>0){?>
    <a class="paginatorPage" href="<?=$this->go(array("page"=>$this->paginatorPage-1))?>"><img src="<?=$this->scope->assets->getIcon('resultset_previous','png')?>"></i></a>
<?}?>
<?for($i=(($this->paginatorPage>$pageLimit)?$this->paginatorPage-$pageLimit:1); $i<=(($page_count-$this->paginatorPage>$pageLimit)?$this->paginatorPage+$pageLimit:$page_count); $i++){?>
    <?$isActive = ($this->paginatorPage==$i)?" active":""?>
        <a class="paginatorPage<?=$isActive?>" href="<?=$this->go(array("page"=>$i))?>"><?=$i?></a>
<?}?>
<?if($this->paginatorPage+1<=$page_count){?>
    <a class="paginatorPage" href="<?=$this->go(array("page"=>$this->paginatorPage+1))?>"><img src="<?=$this->scope->assets->getIcon('resultset_next','png')?>"></i></a>
<?}?>
<?if($this->paginatorPage<$page_count){?>
<a class="paginatorPage" href="<?=$this->go(array("page"=>$page_count))?>"><img src="<?=$this->scope->assets->getIcon('resultset_last','png')?>"></i></a>
<?}?>