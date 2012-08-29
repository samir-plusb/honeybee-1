<?php
    $widget_options = json_encode($t['config_widget_options']);
?>
<section class="container-fluid container-first category-matching-widget" data-category-options='<?php echo $widget_options; ?>'>
    <h2>Zuordnung von Quell-Kategorien (z.B.: TIP) zu BeFi-Branchen</h2>
    <table class="data-list table table-striped table-bordered">
        <thead>
            <tr>
                <th class="data-header">Quell-Kategorie</th>
                <th class="data-header">BeFi Branchen</th>
            </tr>
        </thead>
        <tbody data-bind="foreach: rows">
            <tr>
                <td class="src-categories" data-bind="html: $data.ext_category_display"></td>                
                <td class="mapped-categories">
                    <section data-bind="attr: { 'class': $data.selector }"></section>
                </td>
            </tr>
        </tbody>
    </table>
</section>