(function () {
define(
[
	// No dependencies...
],
/**
 * The model.
 *
 * @since  4.8.0.0
 */
function() {

	/**
	 * Define the Popup Model.
	 */
	var PopupModel = Upfront.Models.ObjectModel.extend({

		// ========== Init
		init: function() {
			/*
			 * All static properties are defined in php class Upfront_PopupView
			 * and are accessed here via `Upfront.data.upfront_popup.defaults`
			 */

			var properties = _.clone( Upfront.data.upfront_popup.defaults );

			properties.element_id = Upfront.Util.get_unique_id(
				properties.id_slug + '-object'
			);

			this.init_properties( properties );
		}

	});

	// Return the module object.
	return PopupModel;

});
})();