<td>
    <!-- ko if: 'nobody' === <?php echo '$data.display_data.'.$field->getName(); ?> -->
        <span>&nbsp;</span>
    <!-- /ko -->

    <!-- ko if: 'nobody' != <?php echo '$data.display_data.'.$field->getName(); ?> -->
        <!-- ko if: '<?php echo $user->getAttribute('login') ?>' === <?php echo '$data.display_data.'.$field->getName(); ?> -->
        <span class="label label-info release-ticket" 
              data-bind="text: <?php echo '$data.display_data.'.$field->getName(); ?>, 
                         click: function() { $parent.ctrl.releaseTicket($data, this); }"></span>
        <!-- /ko -->

        <!-- ko if: '<?php echo $user->getAttribute('login') ?>' !== <?php echo '$data.display_data.'.$field->getName(); ?> -->
        <span class="label" data-bind="text: <?php echo '$data.display_data.'.$field->getName(); ?>"></span>
        <!-- /ko -->
    <!-- /ko -->
</td>