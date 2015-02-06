
var getBitsharesLoginURL = function(callbackURL, bitsharesloginURL){
        var formData = {callback:callbackURL};
        jQuery.ajax({
            url: bitsharesloginURL,
            type: 'post',
            dataType: 'json',
            timeout: 15000,
            data: formData,
  
            error:function(jqXHR, textStatus, errorThrown){
                var res = textStatus;
                if(jqXHR.responseText !== "")
                {
                    res = jqXHR.responseText;
                }
                alert(res);
                              
            },                              
            success: function(response, textStatus, jqXHR) {
                if(response)
                {
                    if(response.error)
                    {
                       alert(response.error);                                            
                    }
                    else if(response.url)
                    {
                        window.location.href = response.url;                        
                    }
			    }
			    else 
			    {
			        alert('Error: Empty response from Bitshares client!');   
			    }                                               
            }
        });
};