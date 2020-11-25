jQuery(document).ready(function ($) {
    const podcastor_player = new Plyr('#podcaster-player');
    function ajaxResquest(url, data, cb_success) {
		$.ajax({
			url: url,
			data: data,
			type: "POST",
			dataType: "json",
			cache: false,
			complete: function (response) {
				cb_success(response)
			}
		});
    }
    podcastor_player.on("playing", function (e){
        var child = $(e.target).children("#podcaster-player")
        
        ajaxResquest(Podcastor.ajax_url, {action: 'podcastor', action_type: 'save_view', post: child.data("post")}, function (response){
            console.log(response)
        })
    })
})