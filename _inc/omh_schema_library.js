



jQuery( function ( $ ) {

  $('.update-button').on('click', function() {

    window.location = "//" + $( this ).data('url');

  });

  var disableForm = function() {

    $('.update-button').attr('disabled', true);
    $('.update-button').off();
    $('.update-button').attr('href','#');
    $('.update-button-disable-message').show();

  };

  $('*[data-behavior="disable-update"]').on('change', disableForm);
  $('*[data-behavior="disable-update"]').on('keyup', disableForm);

});


// URL encode plugin
jQuery.extend({URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;}
});
