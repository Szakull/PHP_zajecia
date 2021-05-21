<section class="images-table">
<form action="<?=$this->baseurl?>" method="post">
    <input type="text" name="search" placeholder="Wyszukaj opis" style="text-align: center;">
    <input type="text" name="user" placeholder="Wyszukaj userid" style="text-align: center;">
    <input type="text" name="guest" placeholder="Wyszukaj id ciasteczka" style="text-align: center;">
    <input type="text" name="url" placeholder="Wyszukaj url" style="text-align: center;">
    <input type="text" name="ipadress" placeholder="Wyszukaj Ip adres" style="text-align: center;"><br>
    Data od: <input type="datetime-local" name="date_from" placeholder="Wyszukaj daty od" style="text-align: center; width: 50%"><br>
    Data do: <input type="datetime-local" name="date_to" placeholder="Wyszukaj daty do" style="text-align: center; width: 50%">
    <button type="submit" value="Szukaj" name="submit">Szukaj</button><br><button type="submit" value="Resetuj" name="reset">Resetuj</button>
</form>

<?php if( !$actions ){ ?>
  <p style="text-align: center;">Brak aktywności</p>
<?php }else{ ?>
<table> <th>Id ciasteczka</th><th>Userid</th><th>Opis</th><th>URL</th><th>IP adres</th><th>Data</th><tr>
<?php foreach($actions as $k=>$v){ ?>

  <tr><td><?=$v['guestid']?></td><td><?=$v['userid']?></td><td><?=$v['description']?></td><td><?=$v['url']?></td><td><?=$v['ip']?></td><td><?=$v['date']?></td></tr>

<?php } } 
?>
</section>