<div class="stats">
    <div class="container">
        <% if $Title || $SubTitle %>
            <div class="row">
                <div class="col-md-12">
                    <% if $SubTitle %><h3 class="element__subtitle">$SubTitle</h3><% end_if %>
                    <% if $Title && $ShowTitle %><h2 class="element__title">$Title</h2><% end_if %>
                </div>
            </div>
        <% end_if %>
        <div class="row stats__holder">
            <% if $Stats %>
                <% loop $Stats %>
                <div class="col-md-4 stats__stat--holder <% if $First %>first<% else_if $Last %>last<% end_if %>">
                        <div class="stats__stat">
                            <h3 class="stat__statistic"><span class="number">$StatNumber</span><% if $Label %> $Label<% end_if %></h3>
                            <span class="stat__title">$Title</span>
                        </div>
                    </div>
                <% end_loop %>
            <% end_if %>
        </div>
    </div>
</div>

<% if $Stats %>
    <% require javascript('dynamic/silverstripe-elemental-stat-counters: client/src/counter-up.min.js') %>
    <% require javascript(https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js) %>
    <% require javascript('dynamic/silverstripe-elemental-stat-counters: client/src/counterUp-block_init.js') %>
<% end_if %>
