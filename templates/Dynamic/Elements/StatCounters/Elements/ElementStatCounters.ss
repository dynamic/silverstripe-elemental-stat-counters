<% if $Title && $ShowTitle %><h2 class="element__title">$Title</h2><% end_if %>
<% if $Content %><div class="element__content">$Content</div><% end_if %>

<% if $Stats %>
    <div class="row element__stat__counters">
        <% loop $Stats %>
            <div class="col-md-4 card stats__stat--holder <% if $First %>first<% else_if $Last %>last<% end_if %>">
                <div class="card-body stats__stat">
                    <h3 class=" class="card-title stat__statistic"><span class="number">$StatNumber</span><% if $Label %> $Label<% end_if %></h3>
                    <div class="card-text stat__title">$Title</div>
                </div>
            </div>
        <% end_loop %>
    </div>
<% end_if %>

<% if $Stats %>
    <% require javascript('dynamic/silverstripe-elemental-stat-counters: client/src/counter-up.min.js') %>
    <% require javascript(https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js) %>
    <% require javascript('dynamic/silverstripe-elemental-stat-counters: client/src/counterUp-block_init.js') %>
<% end_if %>
