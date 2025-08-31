<script>
    //if ("AT session('role')" != 'admin') location.href='/module/users/login';
</script>
<!--<h1>ARC PORTAL ADMIN PAGE - AT session('username') (AT session('role'))</h1>-->
<h1>ADMIN PAGE / {{ ucfirst($_SESSION['role']) }}</h1>
<a href="/attorneys">Go to Attorneys Page</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/leads/declined">Pending Reassignment</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/leads/add">Add New Lead</a>
&nbsp;<small>Bluish = Call Center | Greenish = Website</small>
<hr>

<p>
    AT arc_admin_view('leads')
</p>
<div>
    <h4>Links</h4>
    <ul>
    <li><a target="_blank" href="https://form.jotform.com/250883946089069">QUALIFYING PROVIDER REFERRAL AND MARKETING (updated 3/30/25)</a></li>
    <li><a target="_blank" href="https://800tracking.com/">800response Real-time Tracking Reports</a></li>
    <li><a target="_blank" href="https://ivr.teleemc.com/callback.php">IVR Transcripton Reports</a></li>
    <li><a target="_blank" href="https://docs.google.com/document/d/1ikhv9V0wCN2BcpyH_wi-HQe0_n1TXQRiPtsGSkxIy80/edit?tab=t.0">Cell Center Script</a></li>
    <li><a target="_blank" href="/uploads">(Also see the Uploads page for DOCs and PDFs)</a></li>
    </ul>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="attyModal" tabindex="-1" aria-labelledby="helloWorldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helloWorldModalLabel">Assign Lead to Attorney</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table style="width:100%">
                    <tr>
                        <td style="text-align:right;">Attorney:&nbsp;&nbsp;</td>
                        <td>
                            <select name="attorney" id="attorney">
                                AT arc_attorneys(0)
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:right;"><input type="checkbox" name="checkbox" id="checkbox">&nbsp;&nbsp;</td>
                        <td>
                            Send Notification Email
                        </td>
                    </tr>
                </table>

            </div>
            <div class="modal-footer">
                <span id="alert-modal-more-info"></span>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="assign-lead">Assign Lead</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    var lead_id = 0;
    var atty_id  = 0;
    var checkboxValue = 0;

    $('#attorney').on('change', function() {
        atty_id = $(this).val();
        //console.log(atty_id);
    });

    $(document).on('click', '.attylink, .noattylink', function() {
        lead_id = $(this).attr('rel');
        //console.log(lead_id);
    });

    $('input[name="checkbox"]').on('change', function() {
        checkboxValue = $(this).is(':checked') ? 1 : 0;
        //console.log(checkboxValue); // Outputs 1 if checked, 0 if not
    });

    $(function() {
        $('#assign-lead').click(function() {
            var id = 0;

            //console.log('/api/arc/assign/' + lead_id + '/' + atty_id + '/' + checkboxValue);


            fetch('/api/arc/assign/' + lead_id + '/' + atty_id + '/' + checkboxValue )
                .then(response => {
                    if (!response.ok) {
                        alert('Network response was not ok:', response.statusText);
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json(); // or response.text() if expecting plain text
                })
                .then(data => {
                    //console.log(data); // Log the returned result
                    location.reload();
                    return true;
                })
                .catch(error => {
                    alert('Network error:', error);
                    return false;
                });
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        // Select all table header cells
        let headers = document.querySelectorAll("th");

        headers.forEach(th => {
            if (th.textContent.trim() === "Medical") {
                th.setAttribute("title", "Did the victim seek medical attention?");
            }
        });
    });
</script>
