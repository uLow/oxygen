<?o('div.oxygenLoader[style="'.((isset($args[2]) && $args[2]===false)?'display: none;':'').'"]')?>
<?
if(isset($args[0],$args[1])){
	$color = array($args[0], $args[1]);
}else{
	$color = array('#FFFFFF', '#000000');	
}
?>
<style>
#outerBar{
	height: 20px;
	width: 160px;
	border: 1px solid <?=$color[1]?>;
	overflow: hidden;
	background-color: <?=$color[0]?>;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(<?=$color[0]?>), to(<?=$color[1]?>));
	background: -moz-linear-gradient(top, <?=$color[0]?>, <?=$color[1]?>);
	background: -webkit-linear-gradient(top, <?=$color[0]?>, <?=$color[1]?>);
	background: -ms-linear-gradient(top, <?=$color[0]?>, <?=$color[1]?>);
	background: -o-linear-gradient(top, <?=$color[0]?>, <?=$color[1]?>);
	background: linear-gradient(top, <?=$color[0]?>, <?=$color[1]?>);
}

.barLine{
	background-color: <?=$color[0]?>;
	float: left;
	width: 14px;
	height: 120px;
	margin-right: 24px;
	margin-top: -28px;
	-moz-transform: rotate(45deg);
	-webkit-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	-o-transform: rotate(45deg);
	transform: rotate(45deg);
}

.animation{
	width: 236px;
	-moz-animation-name: animation;
	-moz-animation-duration: 0.8s;
	-moz-animation-iteration-count: infinite;
	-moz-animation-timing-function: linear;
	-webkit-animation-name: animation;
	-webkit-animation-duration: 0.8s;
	-webkit-animation-iteration-count: infinite;
	-webkit-animation-timing-function: linear;
	-ms-animation-name: animation;
	-ms-animation-duration: 0.8s;
	-ms-animation-iteration-count: infinite;
	-ms-animation-timing-function: linear;
	-o-animation-name: animation;
	-o-animation-duration: 0.8s;
	-o-animation-iteration-count: infinite;
	-o-animation-timing-function: linear;
	animation-name: animation;
	animation-duration: 0.8s;
	animation-iteration-count: infinite;
	animation-timing-function: linear;
}

#frontBar{
}

@-moz-keyframes animation{
	0%{
		margin-left: 0px;
	}
	100%{
		margin-left: -38px;
	}
}

@-webkit-keyframes animation{
	0%{
		margin-left: 0px;
	}
	100%{
		margin-left: -38px;
	}
}

@-ms-keyframes animation{
	0%{
		margin-left: 0px;
	}
	100%{
		margin-left: -38px;
	}
}

@-o-keyframes animation{
	0%{
		margin-left: 0px;
	}
	100%{
		margin-left: -38px;
	}
}

@keyframes animation{
	0%{
		margin-left: 0px;
	}

	100%{
		margin-left: -38px;
	}
}
</style>
<div id="outerBar" style="position: relative; left: -50%;">
	<div id="frontBar" class="animation">
		<div class="barLine"></div>
		<div class="barLine"></div>
		<div class="barLine"></div>
		<div class="barLine"></div>
		<div class="barLine"></div>
		<div class="barLine"></div>
	</div>
</div>