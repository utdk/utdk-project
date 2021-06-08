---
name: Release
about: Prepare code for a new release
labels: 'release'

---

## Pre-release checks

- [ ] Review the [documentation issues](https://github.austin.utexas.edu/eis1-wcs/utdk_docs/issues) for any pending tasks that relate to the issues resolved; if any have not been completed, put this issue on hold & resolve those documentation tasks

## Release pull request tasks

- [ ] Create release branch from develop, e.g. `release/3.0.0`
- [ ] Open PR for release branch

## Release completion tasks

- [ ] After approval, merge release branch to develop & master:

```
git fetch && git checkout develop && git pull origin develop && git merge --no-ff release/3.0.0
git fetch && git checkout master && git pull origin master && git merge --no-ff release/3.0.0
git tag 3.0.0
git push origin develop && git push origin master && git push origin git tag 3.0.0
```

- [ ] [Create a new release](https://github.austin.utexas.edu/eis1-wcs/utdk-project/releases/new) (version number and release title should be the same (e.g., `3.0.0`)
- [ ] Use [gren](https://github.com/github-tools/github-release-notes) generate the release notes `gren release --api-url=https://github.austin.utexas.edu/api/v3 --repo=utdk-project --username=eis1-wcs --ignore-issues-with="wontfix,release,duplicate,invalid" --override`
