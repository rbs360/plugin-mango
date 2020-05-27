
{css static}
<link href="$app_path/css/#name.css" rel="stylesheet" media="all" />
{/css}
{js static}
<script type="text/javascript" src="$app_path/js/#name.js"></script>
{/js}
#include <fields>
#include <container>
 
[[css | name: style]]


{CALLBACK_LINK}
<div class="ui green message font-rel">
  <div class="header font-rel">
      Адрес внешней системы (вставить в поле в ЛК Mango Office):
  </div>
    <br>
  $link
</div>
{/CALLBACK_LINK}

[[CONTAINER | h: hide]]
    [[field | name: key | type: input | parentClass: w-100 | addLabel: $api_key$ | required:required-mode | value: $key$]]
    [[field | name: salt | type: input | parentClass: w-100 | addLabel: $api_salt$ | required:required-mode | value: $salt$]]
    [[field | name: url | type: hidden | parentClass: w-100 | addLabel: $urlName$ | required:required-mode | value: $url$]]
    [[field | name: transfer | type: checkbox | parentClass: reverseCheckbox | addLabel: $transferName$ | params: $transfer$]]
    [[field | name: createRelationship | type: checkbox | parentClass: reverseCheckbox | addLabel: $createRelationshipName$ | params: $createRelationship$]]
    [[field | name: createDeals | type: checkbox | parentClass: reverseCheckbox | addLabel: $createDealsName$ | params: $createDeals$]]
[[CONTAINER_END]]

<script>
    window.pluginTestSettings = "settings";
</script>

#include <plugin_users>
[[js | name: main]] 
