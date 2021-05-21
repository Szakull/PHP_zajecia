<section>
<?php if($this->u) { ?>
  <form action="<?=$this->baseurl?>" method="post">
     <a name="note_form"></a>
     <header><h2><?=($note)?"Edytuj notatke":"Dodaj nową notatke"?></h2></header>  
     <input type="text" name="notetitle" placeholder="Nowy temat" autofocus value="<?=($note)?$note['notetitle']:""?>"\><br />
     <textarea name="note" cols="80" rows="10" placeholder="Opis nowego tematu" ><?=($note)?$note['note']:""?></textarea><br />
     <input type="hidden" name="noteid" value="<?=($note)?$note['noteid']:"";?>" \>
     <button type="submit" >Zapisz</button>
  </form>

<form action="<?=$this->baseurl?>" method="post">
    <input type="text" name="search" placeholder="Wyszukaj" style="text-align: center;">
    <?=(!$this->context)?'<label style="display: inline-block; margin: 10px;"><input type="radio" name="where" value="userid">Nazwa użytkownika</label>':""?>
    <label style="display: inline-block; margin: 10px;"><input type="radio" name="where" value="notetitle">Tytuł</label>
    <label style="display: inline-block; margin: 10px;"><input type="radio" name="where" value="note" checked>Notatka</label>
    <input type="submit" value="Szukaj">
</form>
<?php } ?>
<?php if( !$notes ){ ?>
  <p style="text-align: center;">Brak notatek</p>
<?php }else{ foreach($notes as $k=>$v){ ?>

  <article class="note">
    <header>Tytuł notatki: <b><?=htmlentities($v['notetitle'])?></b></header>
    <div><?=nl2br(htmlentities($v['note']))?></div>
    <footer>
    <?php if($this->u and ($this->u['userlevel']==10 or $this->u['userid'] == $v['userid'])) { ?>
    <nav>
    <a href="?noteid=<?=$v['noteid']?>&cmd=noteedit">EDYTUJ</a>
    <a class="danger" href="?noteid=<?=$v['noteid']?>&cmd=notedelete">KASUJ</a>
    </nav>
    <?php } ?>
    ID: <?=$v['noteid']?>, Autor: <?=htmlentities($users[$v['userid']]['username'])?>, Utworzono: <?=$v['date']?>
    </footer>
  </article>

<?php } } 
?>

</section>