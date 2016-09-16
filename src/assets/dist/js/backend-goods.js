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
    $('#w1')
        .on('select_node.jstree', function (e, data) {
            var $node = data.node;
            var $instance = data.instance;
            if ($node.a_attr['data-entity_id'] != DotPlantStore.categoryEntityId) {
                $instance.deselect_node($node);
            } else {
                optionData.value = $node.id;
                optionData.text = $node.text;
                changeOptions(optionData);
                $.each($node.parents, function (i, e) {
                    var $parentNode = $instance.get_node(e);
                    if (true === $parentNode.hasOwnProperty('a_attr')) {
                        if ($parentNode.a_attr['data-entity_id'] == DotPlantStore.categoryEntityId) {
                            $instance.select_node($parentNode);
                            optionData.value = $parentNode.id;
                            optionData.text = $parentNode.text;
                            changeOptions(optionData);
                        }
                    }
                });
            }
        }).on('deselect_node.jstree', function (e, data) {
            var $node = data.node;
            optionData.value = $node.id;
            optionData.text = $node.text;
            changeOptions(optionData, true);
        });

    //manipulating options set in the main goods category select
    function changeOptions(optionData, remove) {
        var $select = $(DotPlantStore.mainCategorySelector);
        if (true === remove) {
            $('option[value=' + optionData.value + ']', $select).remove();
            $('.fdbx3894[value=' + optionData.value + ']').remove();
        } else {
            var $option = $('<option></option>');
            if (0 === $('option[value=' + optionData.value + ']', $select).length) {
                var $appendOption = $option.clone().val(optionData.value).text(optionData.text);
                $select.append($appendOption);
            }
            var $input = $('<input type="hidden" class="fdbx3894">');
            var $appendInput = $input
                .clone()
                .attr('name', DotPlantStore.goodsFormName + '[categories][]')
                .val(optionData.value);
            $select.after($appendInput);
        }
    }
})(jQuery);