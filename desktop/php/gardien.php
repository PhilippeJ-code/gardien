<?php
  if (!isConnect('admin')) 
  {
	 throw new Exception('{{401 - Accès non autorisé}}');
  }

  $plugin = plugin::byId('gardien');
  sendVarToJS('eqType', $plugin->getId());
  $eqLogics = eqLogic::byType($plugin->getId());

?>

<div class="row row-overflow">
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
	  <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <div class="eqLogicThumbnailContainer">
      <?php

        // Affiche la liste des équipements
        //
        foreach ($eqLogics as $eqLogic) 
        {
	        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	        echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	        echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	        echo '<br>';
	        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	        echo '</div>';
        }
      ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
	   		<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#horairetab" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i>{{Horaire}}</a></li>
      <li role="presentation"><a href="#surveillancetab" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i>{{Surveillance}}</a></li>
      <li role="presentation"><a href="#actionstab" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i>{{Actions}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                    foreach (jeeObject::all() as $object) 
                    {
	                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
	          <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-9">
                <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) 
                  {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                  }
                ?>
              </div>
            </div>
	          <div class="form-group">
		          <label class="col-sm-3 control-label"></label>
		          <div class="col-sm-9">
			          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			          <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		          </div>
	          </div>

            <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Utiliser le widget du plugin}}</label>
              <div class="col-sm-3 form-check-input">
                <input type="checkbox" required class="eqLogicAttr" data-l1key="configuration"
                  data-l2key="isWidgetPlugin" checked /></label>
              </div>
            </div>

          </fieldset>
        </form>
      </div>

      <!--

        Onglet Horaire

      -->
      <div role="tabpanel" class="tab-pane" id="horairetab">

        <form class="form-horizontal">
          <fieldset>

            <br /><br />

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Cron gardiennage}}
                <sup><i class="fas fa-question-circle tooltips"
                    title="{{Horaire du gardiennage}}"></i></sup>
              </label>
              <div class="col-sm-2">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="configuration"
                    data-l2key="cron_gardiennage" />
                  <span class="input-group-btn">
                    <a class="btn btn-default cursor jeeHelper" data-helper="cron"><i
                        class="fas fa-question-circle"></i></a>
                  </span>
                </div>
              </div>
            </div>

          </fieldset>
        </form>
      </div>

      <!--

        Onglet Surveillance

      -->
      <div role="tabpanel" class="tab-pane" id="surveillancetab">

        <form class="form-horizontal">
          <fieldset>

            <br /><br />

            <form class="form-horizontal">
              <fieldset>
                <div>
                  <legend>
                    {{Equipements ( On teste le délai depuis la dernière communication)}}
                    <a class="btn btn-primary btn-xs pull-right addEquipment" data-type="equipements"
                      style="position: relative; top : 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un équipement}}</a>
                  </legend>
                  <div id="div_equipements">

                  </div>
                </div>
              </fieldset>
            </form>

            <br /><br />

            <form class="form-horizontal">
              <fieldset>
                <div>
                  <legend>
                    {{Commandes ( On teste la valeur de la commande ou la date de collecte)}}
                    <a class="btn btn-primary btn-xs pull-right addCommand" data-type="commandes"
                      style="position: relative; top : 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
                  </legend>
                  <div id="div_commandes">

                  </div>
                </div>
              </fieldset>
            </form>

          </fieldset>
        </form>
      </div>

      <!--

        Onglet Actions

      -->
      <div role="tabpanel" class="tab-pane" id="actionstab">
        <form class="form-horizontal">
          <fieldset>
            <br /><br />

            <form class="form-horizontal">
              <fieldset>
                <div>
                  <legend>
                    {{Actions si pas d'erreur}}
                    <a class="btn btn-primary btn-xs pull-right addAction" data-type="actions_ok"
                      style="position: relative; top : 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une
                      action}}</a>
                  </legend>
                  <div id="div_actions_ok">

                  </div>
                </div>
              </fieldset>
            </form>

            <form class="form-horizontal">
              <fieldset>
                <div>
                  <legend>
                    {{Actions si erreur}}
                    <a class="btn btn-primary btn-xs pull-right addAction" data-type="actions_ko"
                      style="position: relative; top : 5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une
                      action}}</a>
                  </legend>
                  <div id="div_actions_ko">

                  </div>
                </div>
              </fieldset>
            </form>

          </fieldset>
        </form>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th>{{Id}}</th>
              <th>{{Nom}}</th>
              <th>{{Type}}</th>
              <th>{{Paramètres}}</th>
              <th>{{Etat}}</th>
              <th>{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, nom_du_plugin) -->
<?php include_file('desktop', 'gardien', 'js', 'gardien');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
