var CONTENT = "{[$CONTENT]}";

function Request()
{
  this.query = [];
  {[if $PREFS->getPref('security.sslenabled')]}
  this.protocol = document.location.protocol;
  {[/if]}
}

Request.prototype = {
  protocol: 'http',
  method: '',
  path: '',
  query: null,
  nested: null,
  
  getProtocol: function()
  {
    return this.protocol;
  },
  
  setProtocol: function(value)
  {
    this.protocol = value;
  },
  
  getNested: function()
  {
    return this.nested;
  },
  
  setNested: function(value)
  {
    $this.nested = value;
  },
  
  getPath: function()
  {
    return this.path;
  },
  
  setPath: function(value)
  {
    this.path = value;
  },
  
  getMethod: function()
  {
    return this.method;
  },
  
  setMethod: function(value)
  {
    this.method = value;
  },
  
  clearQuery: function()
  {
    this.query = [];
  },
  
  hasQueryVar: function(key)
  {
    return this.query[key] != false;
  },
  
  getQueryVar: function(key)
  {
    return $this.query[key];
  },
  
  setQueryVar: function(key, value)
  {
    this.query[key] = value;
  },
  
  setQueryVars: function(values)
  {
    for (var key in values)
      this.query[key]=values[key];
  },
  
  getQuery: function()
  {
    return this.query;
  },
  
  setQuery: function(value)
  {
    $this.query = value;
  },
  
  encodePath: function()
  {
    var host = '';
    {[if $PREFS->getPref('security.sslenabled')]}
    var thisprotocol = 'http';
    {[else]}
    var thisprotocol = this.protocol;
    {[/if]}
      
    var protocol = document.location.protocol;

    if (thisprotocol != protocol)
      host = thisprotocol + '://{[$SERVER.HTTP_HOST]}';
    
    {[if $PREFS->getPref('url.encoding') eq 'path' ]}
      var url = '/' + this.method;
      var res = this.path;
      if (res.length > 0)
      {
        if (res.substr(0,1) != '/')
          url += '/'+res;
        else
          url += res;
      }
      return host + '{[$PREFS->getPref('url.pagegen')]}' + escape(url);
    {[else]}
      return host + '{[$PREFS->getPref('url.pagegen')]}';
    {[/if]}
  },
  
  makeAllVars: function()
  {
    var newquery = this.query;
    newquery['{[$PREFS->getPref('url.methodvar')]}'] = this.method;
    newquery['{[$PREFS->getPref('url.resourcevar')]}'] = this.path;
    if (this.nested != null)
      newquery['{[$PREFS->getPref('url.nestedvar')]}'] = this.encodeQuery(this.nested.makeAllVars());
    return newquery;
  },
  
  makeVars: function()
  {
    var newquery = this.query;
    if (this.nested != null)
      newquery['{[$PREFS->getPref('url.nestedvar')]}'] = this.encodeQuery(this.nested.makeAllVars());
    {[if $PREFS->getPref('url.encoding') ne 'path']}
      newquery['{[$PREFS->getPref('url.methodvar')]}'] = this.method;
      newquery['{[$PREFS->getPref('url.resourcevar')]}'] = this.path;
    {[/if]}
    return newquery;
  },
  
  encodeQuery: function(query)
  {
    var result = '';
    for (var name in query)
      result += '&' + escape(name) + '=' + escape(query[name]);
    if (result.length > 0)
      result = result.substr(1);
    return result;
  },
  
  encode: function()
  {
    var url = this.encodePath();
    var vars = this.encodeQuery(this.makeVars());
    if (vars.length > 0)
      url += '?' + vars;
    return url;
  }
}
