jQuery(document).ready(function ($) {
    function isUrl(url) {
        try {
            new URL(url);
          } catch (_) {
            return false;  
          }
        
          return true;
    }

    function getPodcastorNoticeTemplate() {
        return `
        <div id="podcastor-notice" class="notice d-none">
            <p></p>
        </div>
        `;
    }

    function showNotice(message='', type='error') {
        var noticeType = '';
        switch (type) {
            case 'error':
                noticeType = 'notice-error'
                break;
            case 'success':
                noticeType = 'notice-success'
                break;
        }
        $('#podcastor-notice p').text(message)
        $('#podcastor-notice').addClass(noticeType).removeClass('d-none');
    }

    function getMediaLinkExt (urlObj){
        const allowedVideoExt = ["mp4", "3gp", "mkv"]
        const allowedAudioExt= ["ogg", "mp3"]
        const ext = urlObj.pathname.split(".")[urlObj.pathname.split(".").length - 1 ]
        if (allowedVideoExt.includes(ext)) {
            return {ext, type: 'video'}
        }else if (allowedAudioExt.includes(ext)){
            return {ext, type: 'audio'}
        }else {
            return false
        }
    }

    function ajaxResquest(data, cb_success) {
		$.ajax({
			url: ajaxurl,
			data: data,
			type: "POST",
			dataType: "json",
			cache: false,
			complete: function (response) {
				cb_success(response)
			}
		});
    }

    $('input[name=podcastor-media-type]').on('change', function(e){
        switch ($(this).val()) {
            case "audio":
                $("#caption-container").addClass('d-none');
                break;
            case "video":
                $("#caption-container").removeClass('d-none');
                break;
            default:
                return true;
                break;
        }
    })
    
    $('a.copy').on('click', function(e){
    
        e.preventDefault();
        
        var codeEle = $(this).parent('.shortcode-container').find('input.sampleLink');
        
        var selection = window.getSelection();
        
        var input = codeEle[0];
        input.select();
        // Copy to the clipboard
        try {
            document.execCommand('copy');
            // copyButton.innerHTML = 'Copied';
            alert('Lien copié !');
        } catch (err) {
            // Unable to copy
            alert('Lien non copié !');
            if (selection.rangeCount != 0) {

            }
        } 
    });
    
    $('div.wrap hr').after(getPodcastorNoticeTemplate());

    $('#post').submit(function() {
        const podcastorLink = $("#podcator-media-url").val()
        
        if (isUrl(podcastorLink)) {
            const url = new URL(podcastorLink)
            var checkExt =  getMediaLinkExt(url)
            if (!checkExt) {
                showNotice("Please choose audio or video media.", "error")
                return false;
            }
           
            switch ($("input[name=podcastor-media-type]:checked").val()) {
                case "audio":
                    var data = {action: 'podcastor', action_type: 'update_media_link', post_id: $("#post_ID").val(), media_link: podcastorLink, media_ext: checkExt.ext, media_type: checkExt.type}
                    break;

                case "video":
                    var data = {action: 'podcastor', action_type: 'update_media_link', post_id: $("#post_ID").val(), media_link: podcastorLink, media_ext: checkExt.ext, media_type: checkExt.type, caption_url: isUrl($("input[name=podcator-caption]").val()) ? $("input[name=podcator-caption]").val() : ""}
                    break;
            
                default:
                    showNotice("Internal error. Please complet all form and try again!", "error")
                    return false;
                    break;
            }
            ajaxResquest(data, function(response){})
            $('#ajax-loading').hide();
            $('#publish').removeClass('button-primary-disabled');
            return true;
        }else{
            showNotice("Please add media link.", "error")
            $('#ajax-loading').hide();
            $('#publish').removeClass('button-primary-disabled');
            return false;
        }
        return false;
    });
});
