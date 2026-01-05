export default {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'body-max-line-length': [1, 'always', 100],
        'header-max-length': [1, 'always', 110],
        'type-enum': [2, 'always', ['build', 'chore', 'ci', 'docs', 'feat', 'feature', 'fix', 'improvement', 'refactor', 'revert', 'style', 'test']],
    },
};
