"use strict";
var DotPlantStore = window.DotPlantStore || {
        missingParamText: 'Missing param'
    };
(function ($) {
    if (false === DotPlantStore.hasOwnProperty('categoryEntityId')) {
        alert(DotPlantStore.missingParamText + ' categoryEntityId');
        return false;
    }
    if (false === DotPlantStore.hasOwnProperty('mainCategorySelector')) {
        alert(DotPlantStore.missingParamText + ' mainCategorySelector');
        return false;
    }
    if (false === DotPlantStore.hasOwnProperty('goodsFormName')) {
        alert(DotPlantStore.missingParamText + ' goodsFormName');
        return false;
    }
    var optionData = {};

    //jstree categories select behavior
    $('#goodsTreeWidget')
        .on('changed.jstree', function (e, data) {
            $('input[data-type="goods_categories"]').remove();
            var i, j, r = [];
            for (i = 0, j = data.selected.length; i < j; i++) {
                var $node = data.instance.get_node(data.selected[i]);
                if ($node.a_attr['data-entity_id'] != DotPlantStore.categoryEntityId) {
                    $instance.deselect_node($node);
                } else {
                    optionData.value = $node.id;
                    optionData.text = $node.text;
                    changeOptions(optionData);
                }
            }
        });

    //manipulating options set in the main goods category select
    function changeOptions(optionData) {
        var $select = $(DotPlantStore.mainCategorySelector);

        var $option = $('<option></option>');
        if (0 === $('option[value=' + optionData.value + ']', $select).length) {
            var $appendOption = $option.clone().val(optionData.value).text(optionData.text);
            $select.append($appendOption);
        }
        var $input = $('<input type="hidden" data-type="goods_categories">');
        var $appendInput = $input
            .clone()
            .attr('name', DotPlantStore.goodsFormName + '[categories][]')
            .val(optionData.value);
        $select.after($appendInput);

    }
})(jQuery);