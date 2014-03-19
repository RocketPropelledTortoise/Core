#git subsplit available here : https://github.com/dflydev/git-subsplit
git subsplit init git@github.com:RocketPropelledTortoise/Core.git
git subsplit publish src/Taxonomy:git@github.com:RocketPropelledTortoise/Taxonomy.git
git subsplit publish src/Translation:git@github.com:RocketPropelledTortoise/Translation.git
git subsplit publish src/Utilities:git@github.com:RocketPropelledTortoise/Utilities.git
rm -rf .subsplit/
