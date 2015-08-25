function file_download(file)
 {
  $("#download").load("miniscript.php?a=download&file="+file);
  alert('pobieranie.'+file);
 }