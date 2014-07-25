<form class="logon-form" action="<?=$this->go('login')?>" method="POST" style="float: right">
	<?$auth = $this->scope->auth?>
	<?if($auth->role):?>
		<span class="user"><?=$auth->login?></span><?//=$this->_("signed_as")?>
		<?/*<select class="role">
			<?foreach($auth->roles as $role):?>
				<option><?=$role?></option>
			<?endforeach?>
		</select>*/?>
		<input type="submit" name="sign-out" value="<?=$this->_("sign_out")?>"/>
	<?else:?>
		<span class="message"><?=$this->_("you_are_not_authorized")?></span>
	<?endif?>
</form>