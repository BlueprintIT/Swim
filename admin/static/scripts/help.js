function openClassHelp(section, class, target)
{
  var request = new Request();
  request.setMethod("admin");
  request.setPath("help/class.tpl");
  request.setQueryVar("section", section);
  request.setQueryVar("class", class);
  window.open(request.encode()+"#"+target, "SWIMHelp", "width=600,height=400,menubar=no,toolbar=no,location=no,directories=no,status=no,resizable");
}

function openClassHelp(section, class, target)
{
  var request = new Request();
  request.setMethod("admin");
  request.setPath("help/view.tpl");
  request.setQueryVar("section", section);
  request.setQueryVar("view", class);
  window.open(request.encode()+"#"+target, "SWIMHelp", "width=600,height=400,menubar=no,toolbar=no,location=no,directories=no,status=no,resizable");
}
