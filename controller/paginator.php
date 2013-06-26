<?$page_count = ceil(count($this->model)/$this->sliceSize)?>
<a class="paginatorPage" href="<?=$this->go(array("page"=>1))?>">&laquo;</a>
<?for($i=1; $i<=$page_count; $i++){?>
	<?$isActive = ($this->paginatorPage==$i)?" active":""?>
	<a class="paginatorPage<?=$isActive?>" href="<?=$this->go(array("page"=>$i))?>"><?=$i?></a>
<?}?>
<a class="paginatorPage" href="<?=$this->go(array("page"=>$page_count))?>">&raquo;</a>