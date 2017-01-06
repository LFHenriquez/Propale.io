jQuery(document).ready(function($) {
		var suggestionEngine = new Bloodhound({
	      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	      queryTokenizer: Bloodhound.tokenizers.whitespace,
	      remote: {
	        url: 'http://propale.io/wp-admin/admin-ajax.php?action=company_name&term=',
	        replace: function(url, query) {
	          return url + query;
	        },
	        transform: function(response) {
	          return response;
	        },
	        ajax: {
	          type: "POST",
	          data: {
	            q: function() {
	              return $('.typeahead').val()
	            }
	          }
	        }
	      }
	    });
	    var suggestionTemplate = function (data) {
	        return '<div class="image-ajax"><img class="image" src="' + data.logo + '"/> <div class="ajax-text"><p class="menu-text">' + data.name + '</p>' + '<p class="price menu-text"> ' + data.domain + '</p></div></div>';
	    }
	    $('.typeahead').typeahead(
	      {
	        highlight: true,
	        minLength:3
	      },
	      {
	        name: 'page',
	        display: 'title',
	        source: suggestionEngine,
	        templates: {
	          notFound: '<p align="center"> not found </p>',
	          suggestion: suggestionTemplate,
	          pending: '<p align="center">Loading</p>'
	        },
	      }).bind('typeahead:select', function(ev, suggestion) {
	      		$("#user_logo").attr("value",suggestion.logo);
	      		$(".typeahead").typeahead('val',suggestion.name);
	    });
});