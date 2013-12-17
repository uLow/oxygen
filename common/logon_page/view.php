<?$this->put_header()?>
<form method="POST" class="login-form" autocomplete="off">
	<div class="warning"><?=$this->scope->auth->message?></div>
	<?if($this->scope->auth->role):?>
	<?$this->put_roles()?>
	<input type="submit" name="sign-out" value="<?=$this->_("sign_out")?>"/>
	<?else:?>
	<?/*if($this->scope->auth->login):?>
		<label><span><?=$this->_("username")?>:</span><input name="login" value="<?=htmlentities($this->scope->auth->login, ENT_QUOTES, 'UTF-8')?>"/></label>
		<label><span><?=$this->_("password")?>:</span><input class="logon-focus" name="password" type="password"/></label>
	<?else:*/?>
		<label><span><?=$this->_("username")?>:</span><input class="logon-focus" name="login"/></label>
		<label><span><?=$this->_("password")?>:</span><input name="password" type="password"/></label>
	<?//endif?>
	<input type="submit" name="authenticate" value="<?=$this->_("sign_in")?>"/>
	<?endif?>
</form>