<section class="user-info"><?php if($this->u){ ?>
<div>

<?php if(!isset($_SESSION["context"]) and $_SESSION["context"] != "mynotes" ) {?><a href="<?=$this->baseurl?>?cmd=mynotes" >Moje notatki</a> <?php } ?>
<?php if(isset($_SESSION["context"]) and $_SESSION["context"] != NULL ) {?><a href="<?=$this->baseurl?>?cmd=home" >Home</a> <?php } ?>
<?php if($this->u['userlevel']==10) {?>
<a href="?cmd=userlist">Lista użytkowników</a>
<?php if(!isset($_SESSION["context"]) and $_SESSION["context"] != "action" ) {?><a href="<?=$this->baseurl?>?cmd=action" >Aktywność</a> <?php }} ?>
</div>
<?php } ?>
<?php if($this->u){ ?>
Zalogowany jako: <?=$this->u['username'];?> (<?=$this->u['userid'];?>) <a href="?cmd=logout" >WYLOGUJ</a>
<?php }
else{?>
<?php if(isset($_SESSION["context"]) and $_SESSION["context"] != NULL ) {?><a href="<?=$this->baseurl?>?cmd=home" >Home</a> <?php } ?>
<?php if(!isset($_SESSION["context"]) or $_SESSION["context"] != "login" ) {?><a href="<?=$this->baseurl?>?cmd=login" >Logowanie</a> <?php } ?>
<?php if(!isset($_SESSION["context"]) or $_SESSION["context"] != "register" ) {?><a href="<?=$this->baseurl?>?cmd=register" >Rejestracja</a> <?php } ?>
<?php } ?>
<?php if(isset($_SESSION['userlist']) and $_SESSION['userlist']){ ?>
<br />
<table><tr><th>Identyfikator</th><th>Nazwa</th><th>Poziom</th><th></th></tr>
<?php foreach($users as $k=>$v){ ?>
<tr>
<td><?=$v['userid']?></td>
<td><?=$v['username']?></td>
<td><?=($v['userlevel']==10)?'admin':'user';?></td>
<td><?php if($v['userid']!='admin'){ ?>
<a href="?cmd=changeuser&userid=<?=$v['userid']?>">Zmień</a>&nbsp;
<a class="danger" href="?cmd=deluser&userid=<?=$v['userid']?>">Kasuj</a>
<?php } ?></td>
</tr>
<?php } ?>
</table>
<?php } ?>
</section>