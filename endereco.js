import Promise from 'promise-polyfill';
import merge from 'lodash.merge';
import EnderecoIntegrator from '../js-sdk/modules/integrator';
import css from './endereco.scss';

if ('NodeList' in window && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}

if (!window.Promise) {
    window.Promise = Promise;
}

EnderecoIntegrator.postfix = {
    ams: {
        countryCode: 'land',
        postalCode: 'plz',
        locality: 'ort',
        streetFull: '',
        streetName: 'strasse',
        buildingNumber: 'hausnummer',
        addressStatus: 'enderecoamsstatus',
        addressTimestamp: 'enderecoamsts',
        addressPredictions: 'enderecoamspredictions',
        additionalInfo: 'adresszusatz',
    },
    personServices: {
        salutation: 'anrede',
        firstName: 'vorname'
    },
    emailServices: {
        email: 'email'
    }
};

EnderecoIntegrator.css = css[0][1];
EnderecoIntegrator.resolvers.countryCodeWrite = function(value) {
    return new Promise(function(resolve, reject) {
        resolve(value.toUpperCase());
    });
}
EnderecoIntegrator.resolvers.countryCodeRead = function(value) {
    return new Promise(function(resolve, reject) {
        resolve(value.toLowerCase());
    });
}
EnderecoIntegrator.resolvers.salutationWrite = function(value) {
    var mapping = {
        'F': 'w',
        'M': 'm'
    } ;
    return new Promise(function(resolve, reject) {
        resolve(mapping[value]);
    });
}
EnderecoIntegrator.resolvers.salutationRead = function(value) {
    var mapping = {
        'w': 'F',
        'm': 'M'
    } ;
    return new Promise(function(resolve, reject) {
        resolve(mapping[value]);
    });
}

if (window.EnderecoIntegrator) {
    window.EnderecoIntegrator = merge(window.EnderecoIntegrator, EnderecoIntegrator);
} else {
    window.EnderecoIntegrator = EnderecoIntegrator;
}

window.EnderecoIntegrator.asyncCallbacks.forEach(function(cb) {
    cb();
});
window.EnderecoIntegrator.asyncCallbacks = [];

window.EnderecoIntegrator.waitUntilReady().then( function() {
    var $removeTypeahead = setInterval( function() {
        if (
            $ &&
            $('[name="ort"]').length &&
            $('[name="ort"]')[0].classList.contains('tt-input')
        ) {
            $('.city_input').typeahead('destroy');
            clearInterval($removeTypeahead);
        }
    }, 100);

    var $removeTypeahead2 = setInterval( function() {
        if (
            $ &&
            $('[name="register[shipping_address][ort]"]').length &&
            $('[name="register[shipping_address][ort]"]').typeahead
        ) {
            $('[name="register[shipping_address][ort]"]').typeahead('destroy');
            clearInterval($removeTypeahead2);
        }
    }, 100);
});

