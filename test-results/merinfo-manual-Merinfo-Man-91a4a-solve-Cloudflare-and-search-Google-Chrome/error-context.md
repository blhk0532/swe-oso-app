# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - main [ref=e2]:
    - generic [ref=e3]:
      - heading "www.merinfo.se" [level=1] [ref=e4]
      - paragraph [ref=e5]: Bekräfta att du är en människa. Det kan ta några sekunder.
      - generic [ref=e12]: www.merinfo.se måste kontrollera säkerheten för din anslutning innan du kan fortsätta.
      - alert [ref=e13]:
        - text: Verifieringen tar längre tid än förväntat. Kontrollera internetanslutningen. Om problemet kvarstår
        - link "uppdaterar du sidan" [ref=e14] [cursor=pointer]:
          - /url: "#"
        - text: .
  - contentinfo [ref=e15]:
    - generic [ref=e16]:
      - generic [ref=e18]:
        - text: "Ray ID:"
        - code [ref=e19]: 99b5e3a8c868d334
      - generic [ref=e20]:
        - text: Prestanda och säkerhet från
        - link "Cloudflare" [ref=e21] [cursor=pointer]:
          - /url: https://www.cloudflare.com?utm_source=challenge&utm_campaign=j
```