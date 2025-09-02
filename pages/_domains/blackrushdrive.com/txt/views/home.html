<script>

    /*

    Todo:
        Save the user's location
        Register the user (email address)
        Add Actions and Outcomes

        Inventory system
        Map

        Scene editor - a form to edit scenes, mode to click "na" buttons to create new locations, action options

        Sound? Narration? (auto or manual)

        A visited_desc text for when you were already in a scene in the same frame

     */

    $(function() {
        $('#east, #west, #north, #south').on('click', function() {
            var rel = $(this).attr('rel');
            go(rel);
        });

    });

    function go(where = 0) {
        $.ajax({
            type: "POST",
            url: "/api",
            dataType: "json",
            data: {
                'where':where,
                // Command (NSEW, etc)
            },
            success: function(o) {
                console.log(o);
                blit(o);
            },
            error: function(e) {
                console.log(e);
                alert('An error has occurred');
            }
        });
    }

    function blit(o) {

        $('#start').hide();
        $('#north').prop("disabled", true).attr('title', 'na').attr('rel', '');
        $('#south').prop("disabled", true).attr('title', 'na').attr('rel', '');
        $('#east').prop("disabled", true).attr('title', 'na').attr('rel', '');
        $('#west').prop("disabled", true).attr('title', 'na').attr('rel', '');

        $('.action1').prop("disabled", true).attr('title', 'na').attr('rel', '');
        $('.action2').prop("disabled", true).attr('title', 'na').attr('rel', '');
        $('.action3').prop("disabled", true).attr('title', 'na').attr('rel', '');

        $('#action1').html('Nothing to do here!');
        $('#action2').html('');
        $('#action3').html('');

        // Update image

        $('#game-image').attr('src', o.image);

        // Update text areas
        $('#short-desc').html(o.short_desc);
        $('#long-desc').html(o.long_desc);
        $('#more-desc').html(o.more_desc);

        // Enable/Disable movement buttons



        if (typeof(o.north) == 'object') {
            $('#north').prop("disabled", false).attr('title', o.north.short_desc).attr('rel', o.north.id);
        }
        if (typeof(o.south) == 'object') {
            $('#south').prop("disabled", false).attr('title', o.south.short_desc).attr('rel', o.south.id);
        }
        if (typeof(o.east) == 'object') {
            $('#east').prop("disabled", false).attr('title', o.east.short_desc).attr('rel', o.east.id);
        }
        if (typeof(o.west) == 'object') {
            $('#west').prop("disabled", false).attr('title', o.west.short_desc).attr('rel', o.short_desc.id);
        }

        // Update / Enable / Disable action buttons
    }

</script>

<div style="float:left;width:100%;max-width:550px;padding-right:12px;">
    <span id="start">
    <H1>City at World's End II</H1>
    <p>A game script from Blackrush Entertainment</p>
    <br/>
    <p>Enter your email address</p>
    <input disabled type="email" placeholder="na@na.na">
    <br/>
    <br/>
    <button class="btn btn-success" onclick="go()">Start/Continue</button>
    </span>

    <span id="short-desc" style="color:magenta;"></span>
    <hr>
    <span id="long-desc"></span>
    <hr>
    <span id="more-desc"></span>

</div>
<div style="float:left;width:100%;max-width:550px;">
    <img id="game-image" src="@asset('images/art/city/cwe2.jpg')" style="max-width:500px; width:100%;">
</div>
<div style="clear:both;width:100%;height:12px;"></div>
<div style="width:100%;text-align:center;padding:12px;">
    <div style="margin-left:auto;margin-right:auto;">
    <table>
        <tr>
            <th colspan="3">Moves</th>
            <th style="">Actions</th>
        </tr>
        <tr>
            <td></td>
            <td><button disabled id="north" class="btn btn-primary btn-sm">North</button></td>
            <td></td>
            <td align ="left"><button disabled class="action1 btn btn-success btn-sm"><i class="bi bi-twitch"></i></button>&nbsp;<span id="action1">This is an Action button</span></td>
        </tr>
        <tr>
            <td><button disabled id="west" class="btn btn-primary btn-sm">West</button></td>
            <td></td>
            <td><button disabled id="east" class="btn btn-primary btn-sm">East</button></td>
            <td align ="left"><button disabled class="action2 btn btn-warning btn-sm"><i class="bi bi-twitch"></i></button>&nbsp;<span id="action2">Alternative action here</span></td>
        </tr>
        <tr>
            <td></td>
            <td><button disabled id="south" class="btn btn-primary btn-sm">South</button></td>
            <td></td>
            <td align ="left"><button disabled class="action3 btn btn-danger btn-sm"><i class="bi bi-twitch"></i></button>&nbsp;<span id="action3">Probably a bad idea here</span></td>
        </tr>
    </table>
    </div>
</div>

