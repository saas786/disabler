module.exports = {
	"extends": [
		"stylelint-config-twbs-bootstrap"
	],
	"rules": {
		// 'selector-class-pattern': '^([a-z]+).*$', // We have a mix of snake_case, lowerCamelCase and kebab-case
		"value-keyword-case": [ // Don't change case of values such as `currentColor` etc
			"lower",
			{
				"camelCaseSvgKeywords": true
			}
		]
	}
}
