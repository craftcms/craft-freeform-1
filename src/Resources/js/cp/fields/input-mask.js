"use strict";var elements=document.querySelectorAll(".form-phone-pattern-field"),_iteratorNormalCompletion=!0,_didIteratorError=!1,_iteratorError=void 0;try{for(var _iterator=elements[Symbol.iterator](),_step;!(_iteratorNormalCompletion=(_step=_iterator.next()).done);_iteratorNormalCompletion=!0){var elem=_step.value;elem.dataset&&elem.dataset.pattern&&new IMask(elem,{mask:elem.dataset.pattern})}}catch(err){_didIteratorError=!0,_iteratorError=err}finally{try{!_iteratorNormalCompletion&&_iterator["return"]&&_iterator["return"]()}finally{if(_didIteratorError)throw _iteratorError}}