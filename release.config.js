module.exports = {
  "branches": ["main"],
  "tagFormat": ["${version}"],
  "plugins": [
    ["@semantic-release/commit-analyzer", {
      "preset": "angular",
      "parserOpts": {
        "noteKeywords": ["BREAKING CHANGE", "BREAKING CHANGES", "BREAKING"]
      }
    }],
    ["@semantic-release/release-notes-generator", {
      "preset": "angular",
    }],
    ["@semantic-release/changelog", {
      "changelogFile": "CHANGELOG.md"
    }],
    "@semantic-release/github",
    [
      "@google/semantic-release-replace-plugin",
      {
        "replacements": [
          {
            "files": ["src/Version.php"],
            "from": "const VERSION = \'.*\'",
            "to": "const VERSION = \'${nextRelease.version}\'",
            "results": [
              {
                "file": "src/Version.php",
                "hasChanged": true,
                "numMatches": 1,
                "numReplacements": 1
              }
            ],
            "countMatches": true
          },
        ]
      }
    ],
    ["@semantic-release/git", {
      "assets": ["CHANGELOG.md", "src/Version.php"],
      "message": "chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}"
    }],
  ],
  firstRelease: "0.1.0",
}
