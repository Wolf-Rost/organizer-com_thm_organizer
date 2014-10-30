window.addEvent('domready', function() {
    "use strict";
    document.formvalidator.setHandler('germandate',
        function (value) { return (/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/).test(value); });
});
function checkAll()
{
    "use strict";
    var checkbox = document.getElementsByName('eventIDs[]');
    if(checkbox[0].checked === true)
    {
        for (var i = 0; i < checkbox.length; i++)
        {
            checkbox[i].checked = true;
        }
    }
    else
    {
        unCheckAll();
    }
}
function unCheckAll()
{
    "use strict";
    var checkbox = document.getElementsByName('eventIDs[]');
    for (var i = 0; i < checkbox.length; i++)
    {
        checkbox[i].checked = false ;
    }
}
function submitForm(task)
{
    "use strict";
    if(task === 'event.new')
    {
        unCheckAll();
        task = 'event.edit';
    }
    document.getElementById('task').value = task;
    document.getElementById('thm_organizer_el_form').submit();
}
function reSort( col, dir )
{
    "use strict";
    document.getElementById('orderby').value=col;
    document.getElementById('orderbydir').value=dir;
    document.getElementById('thm_organizer_el_form').submit();
}
function resetForm()
{
    "use strict";
    var searchTextInput = document.getElementById('jform_thm_organizer_el_search_text');
    searchTextInput.value='';
    var category = document.getElementById('categoryID');
    var index;
    if(category !== null)
    {
        for(index = 0; index < category.length; index++)
        {
            if(category[index].value === '-1')
            {
                category.selectedIndex = index;
            }
        }
    }
    var limit = document.getElementById('limit');
    if(limit !== null)
    {
        for(index = 0; index < limit.length; index++)
        {
            if(limit[index].value === '10')
            {
                limit.selectedIndex = index;
            }
        }
    }
    document.getElementById('jform_fromdate').value='';
    document.getElementById('jform_todate').value='';
    document.getElementById('thm_organizer_el_form').submit();
}