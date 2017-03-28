/**
 * Created by Edward on 2/28/17.
 */
// First, checks if it isn't implemented yet.
if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined'
				? args[number]
				: match
				;
		});
	};
}

function strRandom(strLen) {
	var strLen=strLen || 6;
	var text = "";
	var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	for( var i=0; i < strLen; i++ )
		text += possible.charAt(Math.floor(Math.random() * possible.length));

	return text;
}