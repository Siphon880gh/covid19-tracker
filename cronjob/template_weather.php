<dl>
  <dt>a. 12am:</dt>
  <dd>{{#if realfeels.0.hour_00}} {{realfeels.0.temp___}} / {{realfeels.0.winds}} / {{realfeels.0.humidity}} {{else}} 0 {{/if}}</dd>
  <br>
    
  <dt>b. 6am:</dt>
  <dd>{{#if realfeels.6.hour_06}} {{realfeels.6.temp___}} / {{realfeels.6.winds}} / {{realfeels.6.humidity}} {{else}} 0 {{/if}}</dd>
  <br>
  
  <dt>c. 1pm:</dt>
  <dd>{{#if realfeels.13.hour_13}} {{realfeels.13.temp___}} / {{realfeels.13.winds}} / {{realfeels.13.humidity}} {{else}} 0 {{/if}}</dd>
  <br>
</dl>