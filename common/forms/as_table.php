<?o()?>
<?$fields = $this->getListFields()?>
<table>
<?foreach($fields as $f):?>
<th><?=$f->name?></th>
<?endforeach?>
<?foreach($this as $child):?>
<tr>
<?foreach($fields as $f):?>
<td><?$f->put_short($child)?></td>    
<?endforeach?>
</tr>
<?endforeach?>
</table>
